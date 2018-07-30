<?php

namespace Drupal\opigno_module\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\opigno_module\Entity\OpignoActivityInterface;
use Drupal\opigno_module\Entity\OpignoModuleInterface;
use Drupal\opigno_module\Entity\UserModuleStatus;
use Drupal\opigno_module\Entity\UserModuleStatusInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OpignoModuleController.
 *
 * @package Drupal\opigno_module
 */
class OpignoModuleController extends ControllerBase {

  /**
   * Get activities related to specific module.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $opigno_module
   *   Opigno module entity object.
   *
   * @return array
   *   Array of module's activities.
   */
  public function moduleActivities(OpignoModuleInterface $opigno_module) {
    /* @todo join table with activity revisions */
    $activities = [];
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $query = $db_connection->select('opigno_activity', 'oa');
    $query->fields('oa', ['id', 'type', 'name']);
    $query->fields('omr', [
      'activity_status',
      'weight', 'max_score',
      'auto_update_max_score',
      'omr_id',
      'omr_pid',
      'child_id',
      'child_vid',
    ]);
    $query->addJoin('inner', 'opigno_module_relationship', 'omr', 'oa.id = omr.child_vid');
    $query->condition('oa.status', 1);
    $query->condition('omr.parent_id', $opigno_module->id());
    if ($opigno_module->getRevisionId()) {
      $query->condition('omr.parent_vid', $opigno_module->getRevisionId());
    }
    $query->condition('omr_pid', NULL, 'IS');
    $query->orderBy('omr.weight');
    $result = $query->execute();
    foreach ($result as $activity) {
      $activities[] = $activity;
    }

    return $activities;
  }

  /**
   * Add activities to existing module.
   *
   * @param array $activities
   *   Array of activities that will be added.
   * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $module
   *   Opigno module entity object.
   *
   * @return bool
   */
  public function activitiesToModule(array $activities, OpignoModuleInterface $module) {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $module_activities_fields = [];
    foreach ($activities as $activity) {
      if ($activity instanceof OpignoActivityInterface) {
        /* @todo Use version ID instead of reuse of ID. */
        $module_activity_fields['parent_id'] = $module->id();
        $module_activity_fields['parent_vid'] = $module->getRevisionId();
        $module_activity_fields['child_id'] = $activity->id();
        $module_activity_fields['child_vid'] = $activity->getRevisionId();
        $module_activity_fields['max_score'] = 10;
        $module_activities_fields[] = $module_activity_fields;
      }
    }
    if (!empty($module_activities_fields)) {
      $insert_query = $connection->insert('opigno_module_relationship')->fields([
        'parent_id',
        'parent_vid',
        'child_id',
        'child_vid',
        'max_score',
      ]);
      foreach ($module_activities_fields as $module_activities_field) {
        $insert_query->values($module_activities_field);
      }
      $insert_query->execute();
    }
    return TRUE;
  }

  /**
   * Method for Take module tab route.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $opigno_module
   *   Opigno module entity object.
   *
   * @return string
   */
  public function takeModule(Request $request, OpignoModuleInterface $opigno_module) {
    /* @var $opigno_module \Drupal\opigno_module\Entity\OpignoModule */
    /* @var $query_options \Symfony\Component\HttpFoundation\ParameterBag */
    $query_options = $request->query;
    // Check Module availability.
    $availability = $opigno_module->checkModuleAvailability();
    if (!$availability['open']) {
      // Module is not available. Based on availability time.
      drupal_set_message($availability['message'], 'warning');
      return $this->redirect('entity.opigno_module.canonical', [
        'opigno_module' => $opigno_module->id(),
      ]);
    }
    // Check Module attempts.
    $allowed_attempts = $opigno_module->get('takes')->value;
    if ($allowed_attempts > 0) {
      // It means, that module attempts are limited.
      // Need to check User attempts.
      $user_attempts = $opigno_module->getModuleAttempts($this->currentUser());
      if (count($user_attempts) >= $allowed_attempts) {
        // User has more attempts then allowed.
        // Check for not finished attempt.
        $active_attempt = $opigno_module->getModuleActiveAttempt($this->currentUser());
        if ($active_attempt == NULL) {
          // There is no not finished attempt.
          drupal_set_message($this->t('Maximum attempts for this module reached.'), 'warning');
          return $this->redirect('entity.opigno_module.canonical', [
            'opigno_module' => $opigno_module->id(),
          ]);
        }
      }
    }

    // Get activities from the Module.
    $activities = $opigno_module->getModuleActivities();
    if (count($activities) > 0) {
      // Create new attempt or resume existing one.
      $attempt = $opigno_module->getModuleActiveAttempt($this->currentUser());
      if ($attempt == NULL) {
        // No existing attempt, create new one.
        $attempt = UserModuleStatus::create([]);
        $attempt->setModule($opigno_module);
        $attempt->setFinished(0);
        $attempt->save();
      }
      else {
        // Not finished attempt exist. Check if user allowed to resume.
        $allow_resume = $opigno_module->getAllowResume();
        $continue_param = $query_options->get('continue');
        // Continue param will exist only after previous answer form submit.
        if (!$allow_resume && !$continue_param) {
          // If resume is not allowed we need to finish existing attempt.
          $attempt->finishAttempt();
          drupal_set_message($this->t('Module resume is not allowed.'), 'warning');
          // After finish existing attempt we will redirect again to take page to start new attempt.
          return $this->redirect('opigno_module.take_module', [
            'opigno_module' => $opigno_module->id(),
          ]);
        }
      }
      // Get activity that will be answered.
      $next_activity_id = NULL;
      $last_activity_id = $attempt->getLastActivityId();
      $get_next = FALSE;
      // Get additional module settings.
      $backwards_param = $query_options->get('backwards');
      // Take into account randomization options.
      $randomization = $opigno_module->getRandomization();
      if ($randomization > 0) {
        // Get random activity and put it in a sequence.
        $random_activity = $opigno_module->getRandomActivity($attempt);
        if ($random_activity) {
          $next_activity_id = $random_activity->id();
        }
      }
      else {
        foreach ($activities as $activity_id => $activity) {
          // Check for backwards navigation submit.
          if ($opigno_module->getBackwardsNavigation() && isset($prev_activity_id) && $last_activity_id == $activity_id && $backwards_param) {
            $next_activity_id = $prev_activity_id;
            break;
          }
          if (is_null($last_activity_id) || $get_next) {
            // Get the first activity.
            $next_activity_id = $activity_id;
            break;
          }
          if ($last_activity_id == $activity_id) {
            // Get the next activity after this one.
            $get_next = TRUE;
          }
          $prev_activity_id = $activity_id;
        }
      }

      $activities_storage = static::entityTypeManager()->getStorage('opigno_activity');
      if (!is_null($next_activity_id)) {
        // Means that we have some activity to answer.
        $attempt->setCurrentActivity($activities_storage->load($next_activity_id));
        $attempt->save();
        return $this->redirect('opigno_module.answer_form', [
          'opigno_activity' => $next_activity_id,
          'opigno_module' => $opigno_module->id(),
        ]);
      }
      elseif ($attempt->isFinished()) {
        $attempt->finishAttempt();
        return $this->redirect('opigno_module.module_result', [
          'opigno_module' => $opigno_module->id(),
          'user_module_status' => $attempt->id(),
        ]);
      }
    }

    drupal_set_message($this->t('This module has no questions.'), 'warning');
    return $this->redirect('entity.opigno_module.canonical', [
      'opigno_module' => $opigno_module->id(),
    ]);
  }

  /**
   * @param \Drupal\opigno_module\Entity\OpignoActivityInterface $activity
   *
   * @return array
   */
  public function moduleQuestionAnswerFormTitle(OpignoModuleInterface $opigno_module, OpignoActivityInterface $opigno_activity) {
    return $opigno_module->getName();
  }

  /**
   * @param \Drupal\opigno_module\Entity\OpignoActivityInterface $activity
   *
   * @return array
   */
  public function moduleQuestionAnswerForm(OpignoModuleInterface $opigno_module, OpignoActivityInterface $opigno_activity) {
    $build = [];
    $user = $this->currentUser();
    $attempt = $opigno_module->getModuleActiveAttempt($user);
    if (!is_null($attempt)) {
      $existing_answer = $opigno_activity->getUserAnswer($opigno_module, $attempt, $user);
      if (!is_null($existing_answer)) {
        $answer = $existing_answer;
      }
    }
    if (!isset($answer)) {
      $answer = static::entityTypeManager()->getStorage('opigno_answer')->create(array(
        'type' => $opigno_activity->getType(),
        'activity' => $opigno_activity->id(),
        'module' => $opigno_module->id(),
      ));
    }
    // Output rendered activity of the specified type.
    $build[] = \Drupal::entityTypeManager()->getViewBuilder('opigno_activity')->view($opigno_activity, 'activity');
    // Output answer form of the same activity type.
    $build[] = $this->entityFormBuilder()->getForm($answer);

    return $build;
  }

  public function userResults(OpignoModuleInterface $opigno_module) {
    $content = [];
    $results_feedback = $opigno_module->getResultsOptions();
    $user_attempts = $opigno_module->getModuleAttempts($this->currentUser());
    foreach ($user_attempts as $user_attempt) {
      /* @var $user_attempt UserModuleStatus */
      $score_percents = $user_attempt->getScore();
      $max_score = $user_attempt->getMaxScore();
      $score = round(($max_score * $score_percents) / 100);
      foreach ($results_feedback as $result_feedback) {
        // Check if result is between low and high percents.
        // Break on first meet.
        if ($score_percents <= $result_feedback->option_end && $score_percents >= $result_feedback->option_start) {
          $feedback = check_markup($result_feedback->option_summary, $result_feedback->option_summary_format);
          break;
        }
      }
      $content[] = [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('You got %score of %max_score possible points.', [
            '%max_score' => $max_score,
            '%score' => $score,
          ]),
          $this->t('Score: %score%', ['%score' => $user_attempt->getScore()]),
          isset($feedback) ? $feedback : '',
        ],
      ];
    }
    return $content;
  }

  public function userResult(OpignoModuleInterface $opigno_module, UserModuleStatus $user_module_status = NULL) {
    $content = [];
    $user_answers = $user_module_status->getAnswers();
    $question_number = 0;
    $module_activities = $opigno_module->getModuleActivities();
    foreach ($user_answers as $answer) {
      $question_number++;
      $answer_activity = $answer->getActivity();
      $content[] = [
        '#theme' => 'opigno_user_result_item',
        '#opigno_answer' => $answer,
        '#opigno_answer_activity' => $answer_activity ,
        '#question_number' => $question_number,
        '#answer_max_score' => $module_activities[$answer_activity->id()]->max_score,
      ];
    }

    $uid = $this->currentUser()->id();
    $gid = OpignoGroupContext::getCurrentGroupId();
    $cid = OpignoGroupContext::getCurrentGroupContentId();

    if (!empty($gid)) {
      $group_steps = opigno_learning_path_get_steps($gid, $uid);
      $steps = [];

      // Load courses substeps.
      array_walk($group_steps, function ($step) use ($uid, &$steps) {
        if ($step['typology'] === 'Course') {
          $course_steps = opigno_learning_path_get_steps($step['id'], $uid);
          $steps = array_merge($steps, $course_steps);
        }
        else {
          $steps[] = $step;
        }
      });

      // Find current step.
      $count = count($steps);
      $current_step = NULL;

      for ($i = 0; $i < $count; ++$i) {
        $step = $steps[$i];

        if ($step['cid'] === $cid) {
          $current_step = $step;
          break;
        }
      }

      if ($current_step !== NULL) {
        $last_step = end($steps);
        $is_last = $last_step['cid'] === $current_step['cid'];
        $score = $user_module_status->getScore();

        if ($score >= $current_step['required score']) {
          opigno_set_message($uid, $this->t('Successfully completed module @name', [
            '@name' => $current_step['name'],
          ]));

          if ($is_last) {
            $group = Group::load($gid);

            opigno_set_message($uid, $this->t('Congratulations! You successfully finished the training @name', [
              '@name' => $group->label(),
            ]));
          }
        }

        $options = [
          'attributes' => [
            'class' => [
              'btn',
              'btn-success',
              'button',
            ],
            'id' => 'edit-submit',
          ],
        ];

        if (!$is_last) {
          $title = 'Next';
          $route = 'opigno_learning_path.steps.next';
          $route_params = [
            'group' => $gid,
            'parent_content' => $cid,
          ];
        }
        else {
          $title = 'Back to training homepage';
          $route = 'entity.group.canonical';
          $route_params = [
            'group' => $gid,
          ];
        }

        $link['form-actions'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['form-actions'],
            'id' => 'edit-actions',
          ],
          '#title' => 'test',
        ];

        $link['form-actions'][] = Link::createFromRoute(
          $title,
          $route,
          $route_params,
          $options
        )->toRenderable();

        $content[] = $link;
      }
    }

    return $content;
  }

  public function moduleResultsAccess($opigno_module, $user_module_status, AccountInterface $account) {
    $user_module_status = UserModuleStatus::load($user_module_status);
    $permission = $user_module_status->getOwnerId() === $account->id()
      ? 'view own module results'
      : 'view module results';
    return AccessResult::allowedIfHasPermission($account, $permission);
  }

}
