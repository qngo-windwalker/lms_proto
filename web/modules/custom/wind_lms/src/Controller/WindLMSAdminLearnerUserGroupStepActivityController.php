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

class WindLMSAdminLearnerUserGroupStepActivityController extends ControllerBase {

    public function getContent(UserInterface $user, $group, $opigno_module, $opigno_activity) {
        module_load_include('inc', 'wind_lms', 'inc/wind_lms.admin');
        $gid = $group->id();
        $title = $group->label();
        $l = Link::fromTextAndUrl(
            $title . " - GID :  {$gid}",
            Url::fromUri("internal:/group/{$gid}")
        );

        $scormInfo = $this->getScormInfo($opigno_activity, $user->id());
        $attemptInfo = $this->getActivityAttemptsInfo($opigno_module, $opigno_activity, $user, $scormInfo);
        $output = '<h3>' . $l->toString() . '</h3>';
        $output .= 'Attempts total : ' . count($attemptInfo['items']);
        $output .= '<pre>'. print_r($scormInfo, true) . '</pre>';
        $renderable =  [
            '#type' => 'table',
            '#header' => [
                t('Attempt Id'),
                t('User Id'),
                t('Module/Step Id'),
                t('Score'),
                t('Max Score'),
                t('Given Answers'),
                t('Total Questions'),
                t('Percent'),
                t('Last Activity'),
                t('Current Activity'),
                t('Evaluated'),
                t('Started'),
                t('Finished'),
                t('Actual score'),
                t('SCORM data'),
            ],
            '#rows' => $attemptInfo['items'],
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

    public function getTitle(UserInterface $user, $group, $opigno_module, $opigno_activity)  {
        return $opigno_activity->get('name')->value;

    }

    private function getActivityAttemptsInfo($opigno_module, $opignoActivity, $user, $scormInfo) {
        $rows = array();

        /** @var \Drupal\opigno_module\Entity\OpignoActivity $opignoActivity */
        $opignoActivity = $opignoActivity;

        /** @var \Drupal\opigno_module\Entity\UserModuleStatus[] $attempts */
        $attempts = $opigno_module->getModuleAttempts($user);
        if (empty($attempts)) {

        } else {
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus $attempt */
            foreach ($attempts as $id => $attempt) {
                /** @var \Drupal\opigno_module\Entity\OpignoAnswer $answer */
                $answer = $opignoActivity->getUserAnswer($opigno_module, $attempt, $user);
                // Get the scores.
                $actual_score = opigno_learning_path_get_attempt_score($attempt);

                $actualScoreHTML = $answer ? $actual_score : '';
//                    $attemptOutput .= "<p>{$index} {$answerScore} || {$actualScoreHTML} || {$this->getUserActivityScormCompletionStatus($opignoActivity, $user->id())}";

                $started = $attempt->get('started')->getString();
                $finished = $attempt->get('finished')->getString();
                $rows[$id] = array(
                    'data' => array(
                        $id,
                        $attempt->get('user_id')->getString(),
                        $attempt->get('module')->getString(),
                        $attempt->get('score')->getString(),
                        $attempt->get('max_score')->getString(),
                        $attempt->get('given_answers')->getString(),
                        $attempt->get('total_questions')->getString(),
                        $attempt->get('percent')->getString(),
                        $attempt->get('last_activity')->getString(),
                        $attempt->get('current_activity')->getString(),
                        $attempt->get('evaluated')->getString(),
                        $started ? date('m/d/Y', $started) : '',
                        $finished ? date('m/d/Y', $finished) : '',
                        $actualScoreHTML,
                        array(
                            'data' => array(
                                '#markup' => $this->getUserActivityScormScore($user->id(), $scormInfo)
                            )
                        ),
                    )
                );
            }
        }

        return array(
            'items' => $rows,
        );
    }

    /**
     * @param $uid
     * @return string
     */
    private function getUserActivityScormScore($uid, $scormInfo) {
        $output = '';

        // Get SCORM API version.
        $metadata = unserialize($scormInfo['scorm version']);
        if (strpos($metadata['schemaversion'], '1.2') !== FALSE) {
            $scorm_version = '1.2';
            $completion_key = 'cmi.core.lesson_status';
            $raw_key = 'cmi.core.score.raw';
        }
        else {
            $scorm_version = '2004';
            $completion_key = 'cmi.completion_status';
            $raw_key = 'cmi.score.raw';
        }

        // We get the latest result. The way the SCORM API works always overwrites attempts
        // for the global CMI storage. The result stored is always the latest. Get it,
        // and presist it again in the user results table so we can track results through
        // time.

        $completion = opigno_scorm_scorm_cmi_get($uid, $scormInfo['scorm id'], $completion_key, '');
        $successStatus = opigno_scorm_scorm_cmi_get($uid, $scormInfo['scorm id'], 'cmi.success_status', '');

        $output .= '<p>cmi.completion_status: '. $completion . '</p>';
        $output .= '<p>cmi.success_status: '. $successStatus . '</p>';

        return $output;
    }

    /**
     * @param \Drupal\opigno_module\Entity\OpignoActivity $opignoActivity
     * @param $uid
     * @return string
     */
    private function getScormInfo($opignoActivity, $uid) {
        $captureData = array();
        $scorm_controller = \Drupal::service('opigno_scorm.scorm');
        $e = $opignoActivity->get('opigno_scorm_package');
        foreach ($e->referencedEntities() as $file) {
            $scorm = $scorm_controller->scormLoadByFileEntity($file);

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

        }
        return $captureData;
    }

}