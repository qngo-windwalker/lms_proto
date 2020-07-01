<?php

namespace Drupal\wind\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\Core\Controller\ControllerBase;

class WindDatatableController extends ControllerBase {

  public function user_progress() {
    $collection = [];
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1)
      ->condition('roles', 'learner');
    $result = $query->execute();

    if ($result) {
      foreach ($result as $uid) {
        $user_account = user_load($uid);
        $account = \Drupal\user\Entity\User::load($uid);
        $first_name = $account->get('field_user_first_name')->isEmpty() ? '' : $user_account->get('field_user_first_name')->value;
        $last_name = $account->get('field_user_last_name')->isEmpty() ? '' : $user_account->get('field_user_last_name')->value;
        $progress = $this->getUserProgress($account);
        $emailLink = '<a href="mailto:' . $account->getEmail() . '">' . $account->getEmail() . '</a>';
        $collection[] = [$first_name, $last_name, 'email' => $account->getEmail(), 'emailLink' => $emailLink, 'progress' => $progress, 'rowId' => 'uid-' . $uid];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  /**
   * Get user percentage of completion.
   * @param $user
   *
   * @return float
   */
  protected function getUserProgress($user) {
    $grp_membership_service = \Drupal::service('group.membership_loader');
    $grps = $grp_membership_service->loadByUser($user);
    foreach ($grps as $grp) {
      $group = $grp->getGroup();
      $gid = $group->id();
    }
    if (!isset($gid)) {
      return '';
    }
    $uid = $user->id();

    // @see \Drupal\opigno_learning_path\Controller\LearningPathStepsController::start().
    $group_steps = opigno_learning_path_get_steps($group->id(), $uid);
    $steps = [];

    // Load courses substeps.
    // Another look is load all of the opigno_module(s) of the 'group' the user belongs to.
    array_walk($group_steps, function ($step) use ($uid, &$steps) {
      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $uid);
        $steps = array_merge($steps, $course_steps);
      } else {
        $steps[] = $step;
      }
    });

    $completionCount = 0;
    $activitiesNum = 0;
    foreach ($steps as $step) {
      $stepActivities = $this->getStepActivitiesData($step, $uid);
      $activitiesNum += count($stepActivities['activities']);
      foreach ($stepActivities['activities'] as $activity) {
        if($activity['complete_state'] == 'completed'){
          $completionCount ++;
        }
      }
    }

    $progress = ($completionCount / $activitiesNum );
    $progress = round(100 * $progress);
    return $progress;

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


  private function getStepActivitiesData($step, $uid) {
    $collection = array();
    $collection['step_name'] = '<h3>' . $step['name'] . '</h3>';
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

      $activities = $this->getStepActivities($step);
      foreach ($activities as $activity) {
        $collection['activities'][] = array(
          'complete_state' => $this->getActivityCompleteState($activity, $uid),
        );
      }
    } else {
      $collection['activities'][] = array(
        'complete_state' => $this->getActivityCompleteState($step['activities'], $uid),
      );
    }
    return $collection;
  }

  private function getActivityCompleteState($activity, $uid) {
    $e = $activity->get('opigno_scorm_package');
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');

    foreach ($e->referencedEntities() as $file) {
      $scorm = $scorm_controller->scormLoadByFileEntity($file);
      $data = NULL;
      $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
        ->fields('o', array('value', 'serialized'))
        ->condition('o.uid', $uid)
        ->condition('o.scorm_id', $scorm->id)
        ->condition('o.cmi_key', 'cmi.completion_status')
        ->execute()
        ->fetchObject();

      if (isset($result->value)) {
        $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
        return $data;
      }
    }
    return null;
  }
}
