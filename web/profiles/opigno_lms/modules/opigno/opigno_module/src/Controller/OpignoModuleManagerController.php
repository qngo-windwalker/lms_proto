<?php

namespace Drupal\opigno_module\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\h5p\Entity\H5PContent;
use Drupal\h5p\H5PDrupal\H5PDrupal;
use Drupal\h5peditor\H5PEditor\H5PEditorUtilities;
use Drupal\image\Entity\ImageStyle;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoModuleInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for all the actions of the Opigno module manager.
 */
class OpignoModuleManagerController extends ControllerBase {

  protected $H5PActivitiesDetails;

  function __construct()
  {
      $this->H5PActivitiesDetails = $this->getH5PActivitiesDetails();
  }

  /**
   * Check the access for the results page.
   */
  public function access(OpignoModuleInterface $opigno_module, AccountInterface $account) {
    if (empty($opigno_module) || !is_object($opigno_module)) {
      return AccessResult::forbidden();
    }

    if (!$account->hasPermission('edit module entities')
      || !$account->hasPermission('edit activity entities')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * Method called when the manager needs a create or edit form.
   */
  public function getItemForm(OpignoModuleInterface $opigno_module, $type = NULL, $item = 0) {
    // Get the good form from the corresponding content type.
    if ($item > 0) {
      $entity = OpignoActivity::load($item);
      if (!$entity->access('update')) {
        throw new AccessDeniedHttpException();
      }
    }
    else {
      $entity = OpignoActivity::create([
        'type' => $type,
      ]);
    }

    /** @var \Drupal\Core\Entity\EntityFormBuilder $form_builder */
    $form_builder = \Drupal::service('entity.form_builder');
    $form_build = $form_builder->getForm($entity, 'default');

    $form_build['#attached']['library'][] = 'opigno_module/ajax_form';

    // Returns the form.
    return $form_build;
  }

  public static function ajaxFormEntityCallback(&$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If errors, returns the form with errors and messages.
    if ($form_state->hasAnyErrors()) {
      return $form;
    }

    $entity = $form_state->getBuildInfo()['callback_object']->getEntity();

    $item = [];
    $item['id'] = $entity->id();
    $item['name'] = $entity->getName();

    $command = new SettingsCommand([
      'formValues' => $item,
      'messages' => drupal_get_messages(null, true),
    ], TRUE);

    $response->addCommand($command);
    return $response;
  }

  /**
   * Submit handler added in the form
   * via the function opigno_module_form_alter().
   */
  public static function ajaxFormEntityFormSubmit($form, FormState &$form_state) {
    // Gets back the content type and module id.
    $build_info = $form_state->getBuildInfo();
    $params = \Drupal::routeMatch()->getParameters();

    $module = $params->get('opigno_module');
    $type_id = $params->get('type');
    $item_id = $params->get('item');

    // If one information missing, return an error.
    if (!isset($module) || !isset($type_id)) {
      // TODO: Add an error message here.
      return;
    }

    // Get the newly or edited entity.
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $build_info['callback_object']->getEntity();

    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';

    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys)
        && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }

    // Store new entity for display in the AJAX callback.
    $input['entity'] = $entity;
    $form_state->setUserInput($input);

    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);

    // Assign activity to module if entity is new.
    if (!isset($item_id)) {
      /** @var \Drupal\opigno_module\Controller\OpignoModuleController $opigno_module_controller */
      $opigno_module_controller = \Drupal::service('opigno_module.opigno_module');
      $opigno_module_controller->activitiesToModule([$entity], $module);
    }
  }

  /**
   * Get the list of the existing activity types.
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getActivityTypes() {
    // Get activity types.
    $types = \Drupal::entityTypeManager()->getStorage('opigno_activity_type')->loadMultiple();
    $types = array_filter($types, function ($type) {
      /** @var \Drupal\opigno_module\Entity\OpignoActivityType $type */
      return $type->id() !== 'opigno_h5p';
    });

    $types = array_map(function ($type) {
      /** @var \Drupal\opigno_module\Entity\OpignoActivityType $type */
      return [
        'bundle' => $type->id(),
        'name' => $type->label(),
        'description' => $this->getNonH5PDescription($type),
      ];
    }, $types);

    // Get H5P libraries.
    /** @var \Drupal\h5p\H5PDrupal\H5PDrupal $interface */
    $interface = H5PDrupal::getInstance();
    $libraries = $interface->loadLibraries();

    // Flatten libraries array.
    $libraries = array_map(function ($library) {
      return $library[0];
    }, $libraries);

    // Filter runnable libraries.
    $libraries = array_filter($libraries, function ($library) {
      return $library->runnable == 1;
    });

    // Get library data.
    $libraries = array_map(function ($library) {
      return [
        'bundle' => 'opigno_h5p',
        'library' => $library->name,
        'name' => 'H5P ' . $library->title,
        'description' => $this->getH5PDescription($library->name),
      ];
    }, $libraries);

    $types = array_merge($types, $libraries);

    return new JsonResponse($types, Response::HTTP_OK);
  }

  public function getH5PActivitiesDetails()
  {
    $editor = H5PEditorUtilities::getInstance();
    $content_types = $editor->ajaxInterface->getContentTypeCache();
    return $content_types;
  }

  public function getNonH5PDescription(\Drupal\opigno_module\Entity\OpignoActivityType $activity)
  {
    $html = null;

    $html .= '<p class="summary">' . $activity->getSummary() . '</p>';
    $html .= '<p class="description">' . $activity->getDescription() . '</p>';

    if ($image_id = $activity->getImageId()) {
        if ($image = \Drupal\file\Entity\File::load($image_id)) {
            $image_url = ImageStyle::load('large')->buildUrl($image->getFileUri());
            $html .= '<p class="images">';
            $html .= '<img src="' . $image_url . '" alt="" />';
            $html .= '</p>';
        }
    }

    return $html;
  }

  public function getH5PDescription($libTitle)
  {
      $html = null;

      foreach ($this->H5PActivitiesDetails as $H5PActivityDetail) {
          if ($H5PActivityDetail->machine_name == $libTitle) {
              $html .= '<p class="summary">' . $H5PActivityDetail->summary . '</p>';
              $html .= '<p class="description">' . $H5PActivityDetail->description . '</p>';

              $screenshots = json_decode($H5PActivityDetail->screenshots);
              if ($screenshots) {
                  $html .= '<p class="images">';
                  foreach ($screenshots as $screenshot) {
                      $html .= '<img src="' . $screenshot->url . '" alt="" />';
                  }
                  $html .= '</p>';
              }
              break;
          }
      }

      return $html;
  }

  /**
   * Get the list of the existing activities.
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getActivitiesList() {
    $activities = \Drupal::entityTypeManager()
      ->getStorage('opigno_activity')
      ->loadMultiple();

    $list = array_map(function ($activity) {
      /** @var \Drupal\opigno_module\Entity\OpignoActivity $activity */
      $data = [];
      $data['name'] = $activity->label();
      $data['activity_id'] = $activity->id();
      $data['type'] = $activity->bundle();

      // If H5P content, add library info.
      if ($data['type'] === 'opigno_h5p') {
        $value = $activity->get('opigno_h5p')->getValue();
        if ($value && $activity->get('opigno_h5p')->getValue()[0]['h5p_content_id'] !== NULL) {
          $cid = $activity->get('opigno_h5p')->getValue()[0]['h5p_content_id'];

          if ($content = H5PContent::load($cid)) {
            $library = $content->getLibrary();
            $data['library'] = $library->name;
          }
        }
      }

      return $data;
    }, $activities);

    return new JsonResponse($list, Response::HTTP_OK);
  }

  /**
   * Add existing activity to the module.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModule $opigno_module
   * @param \Drupal\opigno_module\Entity\OpignoActivity $opigno_activity
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function addActivityToModule(OpignoModule $opigno_module, OpignoActivity $opigno_activity, Request $request) {
    $opigno_module_controller = \Drupal::service('opigno_module.opigno_module');
    $opigno_module_controller->activitiesToModule([$opigno_activity], $opigno_module);

    return new JsonResponse([], Response::HTTP_OK);
  }

  /**
   * Update activity weight.
   */
  public function activityUpdateWeight(Request $request) {
    $datas = json_decode($request->getContent());
    if (empty($datas->acitivies_weight)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }

    // Check that current user can edit parent Opigno modules.
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $omr_ids = array_map(function ($value) {
      return $value->omr_id;
    }, $datas->acitivies_weight);
    $module_ids = $db_connection
      ->select('opigno_module_relationship', 'omr')
      ->fields('omr', ['parent_id'])
      ->condition('omr_id', $omr_ids, 'IN')
      ->groupBy('parent_id')
      ->execute()
      ->fetchCol();
    $modules = OpignoModule::loadMultiple($module_ids);
    foreach ($modules as $module) {
      /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
      if ($module->access('update')) {
        throw new AccessDeniedHttpException();
      }
    }

    foreach ($datas->acitivies_weight as $value) {
      $merge_query = $db_connection->merge('opigno_module_relationship')
        ->keys([
          'omr_id' => $value->omr_id,
        ])
        ->fields([
          'weight' => $value->weight,
        ])
        ->execute();
    }
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

}
