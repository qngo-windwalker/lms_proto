<?php

namespace Drupal\wind_lms\Controller;

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

class WindLMSTakeCourseController extends ControllerBase {

    public function takeCourse(Request $request, OpignoModuleInterface $opigno_module) {
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
            } else {
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
            } else {
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
            } elseif ($attempt->isFinished()) {
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
     * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $opigno_module
     * @param \Drupal\opigno_module\Entity\OpignoActivityInterface $opigno_activity
     *
     * @return array
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     * @see \Drupal\opigno_module\Controller\OpignoModuleController::moduleQuestionAnswerForm
     */
    public function courseActivityForm(OpignoModuleInterface $opigno_module, OpignoActivityInterface $opigno_activity) {
        $build = [];
        $user = $this->currentUser();
//    $opigno_modules = \Drupal::entityTypeManager()->getStorage('opigno_module')->loadMultiple();
//    foreach ($opigno_modules as $opigno_module) {
//    }
//    $activity_types = \Drupal::entityTypeManager()->getStorage('opigno_activity')->loadMultiple();
//    foreach ($activity_types as $opigno_activity) {
//    }
        $attempt = $opigno_module->getModuleActiveAttempt($user);

        if (!is_null($attempt)) {
            /** @var \Drupal\opigno_module\Entity\OpignoAnswer $existing_answer */
            $existing_answer = $opigno_activity->getUserAnswer($opigno_module, $attempt, $user);
            if (!is_null($existing_answer)) {
                $answer = $existing_answer;
            }
        }
        if (!isset($answer)) {
            /** @var \Drupal\opigno_module\Entity\OpignoAnswer $answer */
            $answer = static::entityTypeManager()->getStorage('opigno_answer')->create(array(
                'type' => $opigno_activity->getType(),
                'activity' => $opigno_activity->id(),
                'module' => $opigno_module->id(),
            ));
        }
        // Output rendered activity of the specified type.
        $build[] = \Drupal::entityTypeManager()->getViewBuilder('opigno_activity')->view($opigno_activity, 'activity');
        // Output answer form of the same activity type.
//    $build[] = $this->entityFormBuilder()->getForm($answer);
        $build[0]['#attributes']['class'][] = 'wind-opigno-iframe';
        $build[0]['#attached']['library'][] = 'wind/scorm_player';
        return $build;
    }

}
