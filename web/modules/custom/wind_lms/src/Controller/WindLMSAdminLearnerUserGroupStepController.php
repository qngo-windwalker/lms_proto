<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;

class WindLMSAdminLearnerUserGroupStepController extends ControllerBase {

    public function getContent(UserInterface $user, $group, $opigno_module) {
        module_load_include('inc', 'wind_lms', 'inc/wind_lms.admin');
        $gid = $group->id();
        $title = $group->label();
        $l = Link::fromTextAndUrl(
            $title . " - GID :  {$gid}",
            Url::fromUri("internal:/group/{$gid}")
        );

        $output = '<h3>' . $l->toString() . '</h3>';
        $renderable =  [
            '#type' => 'table',
            '#header' => [
                t('Activities'),
                t('Id'),
                t('Status'),
                t('Type'),
                t('Attempts'),
                t('Max Score'),
                t('Data'),
                t('SCORM Info'),
                t('Actions'),
            ],
            '#rows' => $this->getStepActivityRows($opigno_module, $user, $gid),
            '#attributes' => [
                'class' => ['step_block_table'],
            ],
        ];

        $output .= render($renderable);
        return array(
            'data' => array(
                '#markup' => $output
            )
        );
    }

    public function getTitle(UserInterface $user, $group, $opigno_module)  {
        return $opigno_module->get('name')->value;

    }

    private function getStepActivityRows($step, $user, $gid) {
        $rows = array();

        /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
        $module = $step;
        // Get activities.
        $activities = $module->getModuleActivities();
        /** @var \Drupal\opigno_module\Entity\OpignoActivity[] $activities */

        foreach ($activities as $activity) {

            /** @var \Drupal\opigno_module\Entity\OpignoActivity $opignoActivity */
            $opignoActivity = OpignoActivity::load($activity->id);
            $l = Link::fromTextAndUrl(
                $opignoActivity->get('name')->value,
                Url::fromUri("internal:/admin/structure/opigno_activity/{$activity->id}/edit")
            );

            $attemptOutput = '';
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus[] $attempts */
            $attempts = $module->getModuleAttempts($user);
            if (empty($attempts)) {
                $attemptOutput = 'None attempts';
            } else {
                $attemptOutput = 'Attempts total : ' . count($attempts);
                $index = 1;
                foreach ($attempts as $attempt) {
                    /** @var \Drupal\opigno_module\Entity\OpignoAnswer $answer */
                    $answer = $opignoActivity->getUserAnswer($module, $attempt, $user);

                    // Get the scores.
                    $actual_score = opigno_learning_path_get_attempt_score($attempt);

                    $answerScore = $answer ? 'Answer score: ' . $answer->get('score')->value : 'No Answer';
                    $actualScoreHTML = $answer ? 'Actual score: ' . $actual_score : '';
//                    $attemptOutput .= "<p>{$index} {$answerScore} || {$actualScoreHTML} || {$this->getUserActivityScormCompletionStatus($opignoActivity, $user->id())}";
                    $index ++;
                }
            }

            $rows[$activity->id] = array(
                'data' => array(
                    $l,
                    $activity->id,
                    $opignoActivity->get('status')->value,
                    $opignoActivity->get('type')->getValue()[0]['target_id'],
                    array(
                        'data' => array(
                            '#markup' => $attemptOutput
                        )
                    ),
                    $activity->max_score,
                    array(
                        'data' => array(
                            '#markup' => '<pre>' . print_r($activity, true) . '</pre>'
                        )
                    ),
                    array(
                        'data' => array(
                            '#markup' => $this->getUserActivityScormScore($opignoActivity, $user->id())
                        )
                    ),
                    array(
                        'data' => array(
                            '#markup' => Link::fromTextAndUrl(
                                t('View User Activity'),
                                Url::fromUri("internal:/admin/people/learner-score/{$user->id()}/group/{$gid}/step/{$step->id()}/activity/{$activity->id}")
                            )->toString()
                        )
                    ),
                )
            );
        }

        return $rows;
    }

    /**
     * @param \Drupal\opigno_module\Entity\OpignoActivity $opignoActivity
     * @param $uid
     * @return string
     */
    private function getUserActivityScormScore($opignoActivity, $uid) {
        $output = '';
        $scorm_controller = \Drupal::service('opigno_scorm.scorm');
        $e = $opignoActivity->get('opigno_scorm_package');
        foreach ($e->referencedEntities() as $file) {
            $scorm = $scorm_controller->scormLoadByFileEntity($file);

            $captureData = array();
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
//            $this->opignoScormPlayerScormTree($scorm);

            $captureData = array(
                'scorm id' => $scorm->id,
                'scorm fid' => $scorm->fid,
                'scorm version' => $scorm_version
            );

            $output .= '<pre>'. print_r($captureData, true) . '</pre>';
            // Get the SCO tree.
//          $tree = $this->opignoScormPlayerScormTree($scorm);

        }
        return $output;
    }
}