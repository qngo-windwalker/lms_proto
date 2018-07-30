<?php

namespace Drupal\opigno_learning_path\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\opigno_learning_path\LearningPathAccess;
use Drupal\opigno_learning_path\LearningPathValidator;
use Drupal\opigno_module\Entity\OpignoModule;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for all the actions of the Learning Path content.
 */
class LearningPathContentController extends ControllerBase {

  private $content_types_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpignoGroupContentTypesManager $content_types_manager) {
    $this->content_types_manager = $content_types_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('opigno_group_manager.content_types.manager')
    );
  }

  /**
   * Root page for angular app.
   */
  public function coursesIndex(Group $group, Request $request) {
    // Check if user has uncompleted steps.
    LearningPathValidator::stepsValidate($group);

    $next_link = $this->getNextLink($group);
    $view_type = ($group->get('type')->getString() == 'opigno_course')
      ? 'manager' : 'modules';

    return [
      '#theme' => 'opigno_learning_path_courses',
      '#attached' => ['library' => ['opigno_group_manager/manage_app']],
      '#base_path' => $request->getBasePath(),
      '#base_href' => \Drupal::service('path.current')->getPath(),
      '#learning_path_id' => $group->id(),
      '#view_type' => $view_type,
      '#next_link' => isset($next_link) ? render($next_link) : NULL,
    ];
  }

  /**
   * Root page for angular app.
   */
  public function modulesIndex(Group $group, Request $request) {
    // Check if user has uncompleted steps.
    LearningPathValidator::stepsValidate($group);

    $next_link = $this->getNextLink($group);
    return [
      '#theme' => 'opigno_learning_path_modules',
      '#attached' => ['library' => ['opigno_group_manager/manage_app']],
      '#base_path' => $request->getBasePath(),
      '#base_href' => \Drupal::service('path.current')->getPath(),
      '#learning_path_id' => $group->id(),
      '#module_context' => 'false',
      '#next_link' => isset($next_link) ? render($next_link) : NULL,
    ];
  }

  public function getNextLink(Group $group) {
    $next_link = NULL;

    if ($group instanceof GroupInterface) {
      $current_step = opigno_learning_path_get_current_step();

      $user = \Drupal::currentUser();
      if ($current_step == 4
        && !$user->hasPermission('manage group members in any group')
        && !$group->hasPermission('administer members', $user)) {
        // Hide link if user can't access members overview tab.
        return NULL;
      }

      $next_step = ($current_step < 5) ? $current_step + 1 : NULL;
      $link_text = !$next_step ? t('Publish') : t('Next');
      $next_link = Link::createFromRoute($link_text, 'opigno_learning_path.content_steps', [
        'group' => $group->id(),
        'current' => ($current_step) ? $current_step : 0,
      ], [
        'attributes' => [
          'class' => [
            'btn',
            'btn-success',
            'color-white',
          ],
        ],
      ])->toRenderable();
    }

    return $next_link;
  }

  /**
   * Check the access for the Learning path content pages.
   *
   * @todo correct permission.
   */
  public function access(Group $group, AccountInterface $account) {
    if (empty($group) || !is_object($group)) {
      return AccessResult::forbidden();
    }

    if ($account->hasPermission('manage group content in any group')) {
      // Allow platform-level content managers to access.
      return AccessResult::allowed();
    }

    if (!LearningPathAccess::getGroupAccess($group, $account, 'lp_manager')) {
      return AccessResult::forbidden();
    }

    if (!$group->hasPermission('edit group', $account)) {
      return AccessResult::forbidden();
    }

    $type = $group->getGroupType()->id();
    if ($type !== 'learning_path' && $type !== 'opigno_course') {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * This method is called on learning path load.
   * It returns all the LP courses in JSON format.
   */
  public function getCourses(Group $group) {
    // Init the response and get all the contents from this learning path.
    $courses = [];
    $group_content = $group->getContent('subgroup:opigno_course');
    foreach ($group_content as $content) {
      /* @var $content \Drupal\group\Entity\GroupContent */
      /* @var $content_entity \Drupal\group\Entity\Group */
      $content_entity = $content->getEntity();
      $courses[] = [
        'entity_id' => $content_entity->id(),
        'name' => $content_entity->label(),
      ];
    }

    // Return all the contents in JSON format.
    return new JsonResponse($courses, Response::HTTP_OK);
  }

  /**
   * This method is called on learning path load.
   * It returns all the LP modules in JSON format.
   */
  public function getModules(Group $group) {
    // Init the response and get all the contents from this learning path.
    $modules = [];
    // Get the courses and modules within those.
    $group_content = $group->getContent('subgroup:opigno_course');
    foreach ($group_content as $content) {
      /* @var $content \Drupal\group\Entity\GroupContent */
      /* @var $content_entity \Drupal\group\Entity\Group */
      $course = $content->getEntity();
      $course_contents = $course->getContent('opigno_module_group');
      foreach ($course_contents as $course_content) {
        /* @var $module_entity \Drupal\opigno_module\Entity\OpignoModule */
        $module_entity = $course_content->getEntity();
        $modules[] = [
          'entity_id' => $module_entity->id(),
          'name' => $module_entity->label(),
          'activity_count' => $this->countActivityInModule($module_entity),
          'editable' => $module_entity->access('update'),
        ];
      }
    }
    // Get the direct modules.
    $group_content = $group->getContent('opigno_module_group');
    foreach ($group_content as $content) {
      /* @var $content \Drupal\group\Entity\GroupContent */
      /* @var $content_entity \Drupal\opigno_module\Entity\OpignoModule */
      $content_entity = $content->getEntity();
      $modules[] = [
        'entity_id' => $content_entity->id(),
        'name' => $content_entity->label(),
        'activity_count' => $this->countActivityInModule($content_entity),
        'editable' => $content_entity->access('update'),
      ];
    }

    // Return all the contents in JSON format.
    return new JsonResponse($modules, Response::HTTP_OK);
  }

  public function countActivityInModule(OpignoModule $opigno_module)
  {
      $activities = [];
      /* @var $db_connection \Drupal\Core\Database\Connection */
      $db_connection = \Drupal::service('database');
      $query = $db_connection->select('opigno_activity', 'oa');
      $query->fields('oa', ['id']);
      $query->fields('omr', ['omr_pid', 'child_id']);
      $query->addJoin('inner', 'opigno_module_relationship', 'omr', 'oa.id = omr.child_id');
      $query->condition('oa.status', 1);
      $query->condition('omr.parent_id', $opigno_module->id());
      if ($opigno_module->getRevisionId()) {
        $query->condition('omr.parent_vid', $opigno_module->getRevisionId());
      }
      $query->condition('omr_pid', NULL, 'IS');
      $result = $query->execute();
      $result->allowRowCount = TRUE;

      return $result->rowCount();
  }

  /**
   * This method is called on learning path load.
   * It returns all the activities with the module.
   */
  public function getModuleActivities(OpignoModule $opigno_module) {
    $activities = [];
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $query = $db_connection->select('opigno_activity', 'oa');
    $query->fields('oa', ['id', 'vid', 'type', 'name']);
    $query->fields('omr', [
      'weight',
      'max_score',
      'auto_update_max_score',
      'omr_id',
      'omr_pid',
      'child_id',
      'child_vid',
    ]);
    $query->addJoin('inner', 'opigno_module_relationship', 'omr', 'oa.id = omr.child_id');
    $query->condition('oa.status', 1);
    $query->condition('omr.parent_id', $opigno_module->id());
    if ($opigno_module->getRevisionId()) {
      $query->condition('omr.parent_vid', $opigno_module->getRevisionId());
    }
    $query->condition('omr_pid', NULL, 'IS');
    $query->orderBy('omr.weight');
    $result = $query->execute();
    foreach ($result as $activity) {
      $activities[$activity->id] = $activity;
    }

    // Return all the contents in JSON format.
    return new JsonResponse($activities, Response::HTTP_OK);
  }

  /**
   * This method is called on learning path load.
   * It will update an existing activity relation.
   */
  public function updateActivity(OpignoModule $opigno_module, Request $request) {
    // First, check the params.
    $datas = json_decode($request->getContent());
    if (empty($datas->omr_id) || !isset($datas->max_score)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $merge_query = $db_connection->merge('opigno_module_relationship')
      ->keys([
        'omr_id' => $datas->omr_id,
      ])
      ->fields([
        'max_score' => $datas->max_score,
      ])
      ->execute();
    return new JsonResponse(NULL, Response::HTTP_OK);
  }

  /**
   * This method is called on learning path load.
   * It will update an existing activity relation.
   */
  public function deleteActivity(OpignoModule $opigno_module, Request $request) {
    // First, check the params.
    $datas = json_decode($request->getContent());
    if (empty($datas->omr_id)) {
      return new JsonResponse(NULL, Response::HTTP_BAD_REQUEST);
    }
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $delete_query = $db_connection->delete('opigno_module_relationship');
    $delete_query->condition('omr_id', $datas->omr_id);
    $delete_query->execute();
    return new JsonResponse(NULL, Response::HTTP_OK);
  }
}
