<?php
/**
 * This controller for page [site]/course
 * This page is the landing page after learner logged in or completed registered.
 */

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\Response;

class WindLMSDashboardController extends ControllerBase {

  /**
   * @see \Drupal\opigno_learning_path\Plugin\Block\StepsBlock.
   * @return array
   */
  public function getContent() {
    $user = $this->currentUser();
    return [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'react-container',
      ],
      '#attached' => [
        'library' => [
          'wind_lms/dashboard',
        ],
        'drupalSettings' => [
          'myvar' => 'allo'
        ]
      ],
    ];
    return $response;
  }

  /**
   * @param $step Array(
   * [cid] => 1
   * [id] => 1
   * [name] => Employee Health Module
   * [typology] => Module
   * [time spent] => 79967
   * [completed on] => 1536171883
   * [best score] => 0
   * [required score] => 0
   * [attempts] => 2
   * [activities] => 1
   * [mandatory] => 1
   * )
   *  [cid] : group Content ID
   *
   * @return \Drupal\Core\GeneratedLink
   */
  protected function buildCourseLink($step) {
    if ($step['activities'] > 1) {
      /** @var \Drupal\opigno_group_manager\OpignoGroupContentTypesManager $content_type_manager */
      $content_type_manager = \Drupal::service('opigno_group_manager.content_types.manager');
      // Load step enity
      $stepEntity = OpignoGroupManagedContent::load($step['cid']);

      // Find and load the content type linked to this content.
      /** @var \Drupal\opigno_module\Plugin\OpignoGroupManagerContentType\ContentTypeModule $content_type */
      $content_type = $content_type_manager->createInstance($stepEntity->getGroupContentTypeId());
      // Finally, get the "start" URL
      /** @var \Drupal\Core\Url $step_url */
      $step_url = $content_type->getStartContentUrl($stepEntity->getEntityId());
      //   If no URL, show a message.
      if (empty($step_url)) {
        $renderable = [
          '#type' => 'markup',
          '#markup' => '<p>No URL for the first step.</p>'
        ];

        return render($renderable);
      }

      $output = '<h3>' . $step['name'] . '</h3>';

      $activities = $this->getStepActivities($step);
      foreach ($activities as $activity) {
        $output .= '<p>' . $this->getActivityLink($step['id'], $activity) . '</p>';
      }

      return array('data' => array('#markup' => $output));
    }

    $url = Url::fromRoute(
      'wind.answer_form',
      array('opigno_module' => $step['cid'], 'opigno_activity' => $step['activities']),
      array('attributes' => array('target' => '_blank', 'class' => 'wind-scorm-popup-link'))
    );
    return Link::fromTextAndUrl($step['name'], $url)->toString();
  }

  protected function getStepActivities($step) {
    /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
    $module = OpignoModule::load($step['id']);
    // Get activities.
    $activities = $module->getModuleActivities();
    /** @var \Drupal\opigno_module\Entity\OpignoActivity[] $activities */
    $activities = array_map(function ($activity) {
      return OpignoActivity::load($activity->id);
    }, $activities);

    return $activities;
  }

  protected function getActivityLink($moduleId, $activity) {
    $url = Url::fromRoute(
      'wind.answer_form',
      array('opigno_module' => $moduleId, 'opigno_activity' => $activity->id()),
      array('attributes' => array('target' => '_blank', 'class' => 'wind-scorm-popup-link'))
    );
    return Link::fromTextAndUrl($activity->get('name')->value, $url)->toString();
  }

  protected function buildScore($step) {
    $is_attempted = $step['attempts'] > 0;

    if ($is_attempted) {
      $score = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $step['best score'],
        '#attributes' => [
          'class' => ['lp_steps_block_score'],
        ],
      ];
    } else {
      $score = ['#markup' => '&dash;'];
    }

    return [
      'data' => $score,
    ];
  }

  protected function buildState($step) {
    $is_attempted = $step['attempts'] > 0;
    $is_passed = $step['best score'] >= $step['required score'];

    if ($is_attempted) {
      if ($is_passed) {
        $state = '<span class="lp_steps_block_step_passed"></span>'
          . $this->t('Passed');
      } else {
        $state = '<span class="lp_steps_block_step_failed"></span>'
          . $this->t('Failed');
      }
    } else {
      $state = '<span class="lp_steps_block_step_pending"></span>';
    }

    return [
      'data' => [
        '#markup' => $state,
      ],
    ];
  }

  private function getScormScore($uid, $gid) {

    $scorm_controller = \Drupal::service('opigno_scorm.scorm');

    /** @var \Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent $first_content */
    $step = OpignoGroupManagedContent::getFirstStep($gid);

    while ($step) {
      $id = $step->getEntityId();

      /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
      $module = OpignoModule::load($id);

      // Get activities.
      $activities = $module->getModuleActivities();

      /** @var \Drupal\opigno_module\Entity\OpignoActivity[] $activities */
      $activities = array_map(function ($activity) {
        return OpignoActivity::load($activity->id);
      }, $activities);

      foreach ($activities as $activity) {
        $e = $activity->get('opigno_scorm_package');

        foreach ($e->referencedEntities() as $file) {
          $scorm = $scorm_controller->scormLoadByFileEntity($file);

          $data = NULL;
          $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
            ->fields('o', array('value', 'serialized'))
            ->condition('o.uid', $uid)
            //              ->condition('o.scorm_id', $s->id)
            ->condition('o.cmi_key', 'cmi.completion_status')
            ->execute()
            ->fetchObject();

          if (isset($result->value)) {
            $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
          }


          // Get SCORM API version.
          $metadata = unserialize($scorm->metadata);
          if (strpos($metadata['schemaversion'], '1.2') !== FALSE) {
            $scorm_version = '1.2';
          } else {
            $scorm_version = '2004';
          }

          // Get the SCO tree.
//          $tree = $this->opignoScormPlayerScormTree($scorm);
//          dsm($tree);

        }

      }
    }


  }

  /**
   * Traverse the SCORM package data and construct a SCO tree.
   *
   * @param object $scorm
   *
   * @return array
   */
  protected function opignoScormPlayerScormTree($scorm, $parent_identifier = 0) {
    $tree = [];

    $result = db_select('opigno_scorm_package_scos', 'sco')
      ->fields('sco', array('id'))
      ->condition('sco.scorm_id', $scorm->id)
      ->condition('sco.parent_identifier', $parent_identifier)
      ->execute();

    while ($sco_id = $result->fetchField()) {
//      $sco = $this->scorm_service->scormLoadSco($sco_id);
      $sco = $this->scormLoadSco($sco_id);

//      $children = $this->opignoScormPlayerScormTree($scorm, $sco->identifier);
//
//      $sco->children = $children;

      $tree[] = $sco;
    }

    return $tree;
  }

  public function scormLoadSco($sco_id) {
    $sco = db_select('opigno_scorm_package_scos', 'o')
      ->fields('o', array())
      ->condition('id', $sco_id)
      ->execute()
      ->fetchObject();

    if ($sco) {
      $sco->attributes = $this->scormLoadScormAttributes($sco->id);
    }

    return $sco;
  }

  private function scormLoadScormAttributes($sco_id) {
    $attributes = array();

    $result = db_select('opigno_scorm_package_sco_attributes', 'o')
      ->fields('o', array('attribute', 'value', 'serialized'))
      ->condition('sco_id', $sco_id)
      ->execute();

    while ($row = $result->fetchObject()) {
      $attributes[$row->attribute] = !empty($row->serialized) ? unserialize($row->value) : $row->value;
    }

    return $attributes;
  }
}
