<?php

namespace Drupal\opigno_learning_path\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\opigno_learning_path\Entity\LPResult;
use Drupal\opigno_learning_path\LearningPathAccess;
use Drupal\opigno_learning_path\LearningPathValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LearningPathStepsController extends ControllerBase {
  protected $content_type_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpignoGroupContentTypesManager $content_types_manager) {
    $this->content_type_manager = $content_types_manager;
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
   * Start the learning path. This page will redirect the user to the first learning path content.
   */
  public function start(Group $group) {
    // Create empty result attempt if current attempt doesn't exist.
    // Will be used to detect if user already started LP or not.
    $current_attempt = opigno_learning_path_started($group, \Drupal::currentUser());
    if (!$current_attempt) {
      $result = LPResult::createWithValues($group->id(),
        \Drupal::currentUser()
        ->id(), FALSE,
        0
      );
      $result->save();
    }

    $user = $this->currentUser();

    $uid = $user->id();
    $gid = $group->id();

    // Load group steps.
    $group_steps = opigno_learning_path_get_steps($gid, $uid);
    $steps = [];

    // Load group courses substeps.
    array_walk($group_steps, function ($step) use ($uid, &$steps) {
      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $uid);
        $steps = array_merge($steps, $course_steps);
      }
      else {
        $steps[] = $step;
      }
    });

    // Check if there is resumed step. If is - redirect.
    $step_resumed_cid = opigno_learning_path_resumed_step($steps);
    if ($step_resumed_cid) {
      $content = OpignoGroupManagedContent::load($step_resumed_cid);
      // Find and load the content type linked to this content.
      $content_type = $this->content_type_manager->createInstance($content->getGroupContentTypeId());
      $step_url = $content_type->getStartContentUrl($content->getEntityId());
      // Before redirecting, keep the content ID in context.
      OpignoGroupContext::setCurrentContentId($step_resumed_cid);
      OpignoGroupContext::setGroupId($group->id());
      // Finally, redirect the user to the first step.
      return $this->redirect($step_url->getRouteName(), $step_url->getRouteParameters(), $step_url->getOptions());

    };

    // Get the first step of the learning path. If no steps, show a message.
    $first_step = reset($steps);
    if ($first_step === FALSE) {
      return [
        '#type' => 'markup',
        '#markup' => '<p>No first step assigned.</p>',
      ];
    }

    // Load first step entity.
    $first_step = OpignoGroupManagedContent::load($first_step['cid']);

    // Find and load the content type linked to this content.
    $content_type = $this->content_type_manager->createInstance($first_step->getGroupContentTypeId());

    // Finally, get the "start" URL
    //   If no URL, show a message.
    $step_url = $content_type->getStartContentUrl($first_step->getEntityId());
    if (empty($step_url)) {
      return [
        '#type' => 'markup',
        '#markup' => '<p>No URL for the first step.</p>'
      ];
    }

    // Before redirecting, keep the content ID in context.
    OpignoGroupContext::setCurrentContentId($first_step->id());
    OpignoGroupContext::setGroupId($group->id());

    // Finally, redirect the user to the first step.
    return $this->redirect($step_url->getRouteName(), $step_url->getRouteParameters(), $step_url->getOptions());
  }

  /**
   * Redirect the user to the next step.
   */
  public function nextStep(Group $group, OpignoGroupManagedContent $parent_content) {
    // Get the user score of the parent content.
    // First, get the content type object of the parent content.
    $content_type = $this->content_type_manager->createInstance($parent_content->getGroupContentTypeId());
    $user_score = $content_type->getUserScore(\Drupal::currentUser()->id(), $parent_content->getEntityId());

    // If no no score and content is mandatory, show a message.
    if ($user_score === FALSE && $parent_content->isMandatory()) {
      return [
        '#type' => 'markup',
        '#markup' => '<p>No score provided</p>',
      ];
    }

    $user = $this->currentUser();

    $uid = $user->id();
    $gid = $group->id();
    $cid = $parent_content->id();

    // Load group steps.
    $group_steps = opigno_learning_path_get_steps($gid, $uid);
    $steps = [];

    // Load group courses substeps.
    array_walk($group_steps, function ($step) use ($uid, &$steps) {
      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $uid);
        $steps = array_merge($steps, $course_steps);
      }
      else {
        $steps[] = $step;
      }
    });

    // Find next step.
    $count = count($steps);
    $next_step = NULL;

    for ($i = 0; $i < $count - 1; ++$i) {
      if ($steps[$i]['cid'] === $cid) {
        $next_step = $steps[$i + 1];
        break;
      }
    }

    // If no next step, show a message.
    if ($next_step === NULL) {
      return [
        '#type' => 'markup',
        '#markup' => '<p>No next content provided</p>',
      ];
    }

    // Load next step entity.
    $next_step = OpignoGroupManagedContent::load($next_step['cid']);

    // Before redirect, change the content context.
    OpignoGroupContext::setCurrentContentId($next_step->id());
    OpignoGroupContext::setGroupId($group->id());

    // Finally, redirect the user to the next step URL.
    $next_step_content_type = $this->content_type_manager->createInstance($next_step->getGroupContentTypeId());
    $next_step_url = $next_step_content_type->getStartContentUrl($next_step->getEntityId());
    return $this->redirect($next_step_url->getRouteName(), $next_step_url->getRouteParameters(), $next_step_url->getOptions());
  }

  /**
   * Show the finish page and save the score.
   */
  public function finish(Group $group) {
    // Get the "user passed" status.
    $current_uid = \Drupal::currentUser()->id();
    $user_passed = LearningPathValidator::userHasPassed($current_uid, $group);

    if ($user_passed) {

      // Store the result in database.
      $current_result_attempt = LPResult::getCurrentLPAttempt($group, \Drupal::currentUser());
      if ($current_result_attempt) {
        $current_result_attempt->setHasPassed($user_passed);
        $current_result_attempt->setFinished(REQUEST_TIME);
        $current_result_attempt->save();
      }
      else {
        // Store the result in database.
        $result = LPResult::createWithValues($group->id(), $current_uid, $user_passed, REQUEST_TIME);
        $result->save();
      }

      return ['#markup' => '<p>You passed!</p>'];
    }
    else {
      return ['#markup' => '<p>You failed!</p>'];
    }
  }

  /**
   * @param \Drupal\group\Entity\Group $group
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function contentSteps(Group $group, $current) {
    // Check if user has uncompleted steps.
    LearningPathValidator::stepsValidate($group);

    // If there aren't any uncompleted steps.
    $next_step = ($current < 5) ? $current + 1 : NULL;
    if (!$next_step) {
      return $this->redirect('opigno_learning_path.manager.publish', ['group' => $group->id()]);
    }
    else {
      // Check for existing courses in the LP.
      // If there are no courses - skip courses step.
      $group_courses = $group->getContent('subgroup:opigno_course');
      if ($current == 2 && empty($group_courses)) {
        $next_step++;
      }
      $route = array_search($next_step, opigno_learning_path_get_routes_steps());
      return $this->redirect($route, ['group' => $group->id()]);
    }
  }

  /**
   * @param \Drupal\group\Entity\Group $group
   *
   * @return array
   */
  public function listSteps(Group $group) {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');

    $group_id = $group->id();
    $uid = \Drupal::currentUser()->id();

    $steps = opigno_learning_path_get_steps($group_id, $uid);

    $rows = array_map(function ($step) use ($date_formatter) {
      return [
        $step['name'],
        $step['typology'],
        $date_formatter->formatInterval($step['time spent']),
        $step['best score'],
      ];
    }, $steps);

    return [
      '#type' => 'table',
      '#header' => [
        'Name',
        'Typology',
        'Total time spent',
        'Best score achieved',
      ],
      '#rows' => $rows,
    ];
  }

  /**
   * Check if the user has access to any next content from the Learning Path.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function nextStepAccess(Group $group, OpignoGroupManagedContent $parent_content) {
    // Check if there is a next step and if the user has access to it.
    // Get the user score of the parent content.
    // First, get the content type object of the parent content.
    $content_type = $this->content_type_manager->createInstance($parent_content->getGroupContentTypeId());
    $user_score = $content_type->getUserScore(\Drupal::currentUser()->id(), $parent_content->getEntityId());

    // If no no score and content is mandatory, return forbidden.
    if ($user_score === FALSE && $parent_content->isMandatory()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * Returns access for the start/finish pages.
   */
  public function access(Group $group, AccountInterface $account) {
    if (empty($group) || !is_object($group)) {
      return AccessResult::forbidden();
    }

    if (!LearningPathAccess::getGroupAccess($group, $account, 'start|finish')) {
      return AccessResult::forbidden();
    }

    if (!LearningPathAccess::statusGroupValidation($group, $account)) {
      return AccessResult::forbidden();
    }

    if ($group->getGroupType()->id() == 'learning_path') {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
