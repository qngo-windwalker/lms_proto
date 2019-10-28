<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;

class WindLMSSystemController extends ControllerBase{

    public function systemAdminLearnerScorePage() {
        // todo: checkout http://redsox.local/module/2/my-results
        $headers = [
            $this->t('User'),
            $this->t('Status'),
            [
                'data' => $this->t('Group/Learning Path'),
                'class' => array('checkbox'),
            ]
        ];

        $rows = $this->getAllUser();
        return ['activities_list' =>
            array(
                '#type' => 'table',
                '#id' => 'activities-list-table',
                '#sticky' => TRUE,
                '#tabledrag' => array(
                    array(
                        'action' => 'order',
                        'relationship' => 'sibling',
                        'group' => 'activities-list-order-weight',
                    ),
                ),
                '#weight' => 5,
                '#header' => $headers,
                '#rows' => $rows,
            )
        ];
    }

    private function getAllUser() {
        $collection = array();
        $query = \Drupal::entityQuery('user')
            ->execute();
        $users = User::loadMultiple($query);
        foreach ($users as $uid => $user) {
            $links = [];
            $links['view'] = [
                'title' => $this->t('View'),
                'url' => Url::fromRoute('user.page', ['uid' => $uid]),
            ];
            $userLinks = Link::fromTextAndUrl($user->getAccountName(), new Url(
                'user.page', [
                    'uid' => $uid,
                ]
            ))->toString();

            $userLinks .= '<br />' . Link::fromTextAndUrl(t('Learner Data'), new Url(
                    'wind_lms.admin_people.user', [
                        'user' => $uid,
                    ]
                ))->toString();


            $collection[$uid] = array(
                'data' => [
                    'user' => array('data' => array('#markup' => $userLinks)),
                    '#status' => $user->get('status')->value,
                    'group' => $this->getUserGroup($user),
                ]
            );
        }

        return $collection;
    }

    private function getUserGroup($user) {
        $output = '';
        /** @var \Drupal\group\GroupMembershipLoader $grp_membership_service */
        $grp_membership_service = \Drupal::service('group.membership_loader');
        $grps = $grp_membership_service->loadByUser($user);
        foreach ($grps as $grp) {
            $group = $grp->getGroup();
            $gid = $group->id();

            $group = Group::load($gid);
            $title = $group->label();

            $l = Link::fromTextAndUrl(
                $title . " - GID :  {$gid}",
                Url::fromUri("internal:/group/{$gid}")
            );
            $output .= '<h3>' . $l->toString() . '</h3>';
            $output .= $this->getUserGroupSteps($user, $group);
        }

        return array(
            'data' => array(
                '#markup' => $output
            )
        );
    }

    private function getUserGroupSteps($user, $group) {
        $output = '';
        $uid = $user->id();
        $gid = $group->id();
        $group_steps = opigno_learning_path_get_steps($gid, $uid);
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

        // Calculate score.
        $mandatory_steps = array_filter($steps, function ($step) {
            return $step['mandatory'];
        });
        if (!empty($mandatory_steps)) {
            $score = round(array_sum(array_map(function ($step) {
                    return $step['best score'];
                }, $mandatory_steps)) / count($mandatory_steps));
        } else {
            $score = 0;
        }

        $progress = opigno_learning_path_progress($gid, $uid);
        $progress = round(100 * $progress);

        $is_passed = opigno_learning_path_is_passed($group, $uid);

        if ($is_passed) {
            $state_class = 'lp_steps_block_summary_state_passed';
            $state_title = t('Passed');
        } else {
            $state_class = 'lp_steps_block_summary_state_pending';
            $state_title = t('In progress');
        }

        foreach ($steps as $index => $step) {
            $url = Url::fromRoute(
                'opigno_module.opigno_activities_browser',
                array('opigno_module' => $step['id'])
            );
            $stepLink = Link::fromTextAndUrl($step['name'], $url)->toString();
            $output .= "<h4>{$stepLink} : Typology - {$step['typology']}</h4>";
            $output .= "<p>Attempts: ({$step['attempts']})</p>";
            $output .= $this->getStepCompletedOn($step);
            $activityRows = $this->getStepActivityRows($step, $user);
            $renderable =  [
                '#type' => 'table',
                '#header' => [
                    t('Activity'),
                    t('Id'),
                    t('Status'),
                    t('Type'),
                    t('Max Score'),
                    t('SCORM Score'),
                    t('Attempts'),
                ],
                '#rows' => $activityRows,
                '#attributes' => [
                    'class' => ['step_block_table'],
                ],
            ];
            $output .= render($renderable);
        }


        // Each step is an opigno_module.
        $steps = array_map(function ($step) {
            return [
                'data' => array(
                    $this->buildCourseLink($step),
                    $this->buildScore($step),
                    $this->buildState($step),
                )
            ];
        }, $steps);

        return $output;
    }

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

    private function getStepActivityRows($step, $user) {
        $rows = array();

        /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
        $module = OpignoModule::load($step['id']);
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
                    $attemptOutput .= "<p>{$index} {$answerScore} || {$actualScoreHTML} || {$this->getUserActivityScormCompletionStatus($opignoActivity, $user->id())}";
                    $index ++;
                }
            }

            $rows[$activity->id] = array(
                'data' => array(
                    $l,
                    $activity->id,
                    $opignoActivity->get('status')->value,
                    $opignoActivity->get('type')->getValue()[0]['target_id'],
                    $activity->max_score,
                    array(
                        'data' => array(
                            '#markup' => $this->getUserActivityScormScore($opignoActivity, $user->id())
                        )
                    ),

                    array(
                        'data' => array(
                            '#markup' => $attemptOutput
                        )
                    )
                )
            );
        }

        return $rows;
    }

    private function getStepCompletedOn($step) {
        if ($step['completed on'] > 0) {
            $date = date('m-d-Y', $step['completed on']);
            return 'Completed on: ' . $date;
        }

        return 'InComplete';
    }

    private function getUserActivityScormValue($opignoActivity, $uid) {
        $output = '';
        $scorm_controller = \Drupal::service('opigno_scorm.scorm');
        $e = $opignoActivity->get('opigno_scorm_package');
        foreach ($e->referencedEntities() as $file) {
            $scorm = $scorm_controller->scormLoadByFileEntity($file);

            $data = NULL;
            $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
                ->fields('o', array('value', 'serialized'))
                ->condition('o.uid', $uid)
                ->condition('o.scorm_id', $scorm->id)
                ->condition('o.cmi_key', 'cmi.success_status')
//                ->condition('o.cmi_key', 'cmi.completion_status')
                ->execute()
                ->fetchObject();

            if (isset($result->value)) {
                $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
            }

            $success_status = opigno_scorm_scorm_cmi_get($uid,  $scorm->id, 'cmi.success_status');
            $completion_status = opigno_scorm_scorm_cmi_get($uid,  $scorm->id, 'cmi.completion_status');

            dsm($completion_status);
//            $output .= '<pre>'. print_r($captureData, true) . '</pre>';
            // Get the SCO tree.
//          $tree = $this->opignoScormPlayerScormTree($scorm);

        }

        return $output;
    }

    private function getUserActivityScormCompletionStatus($opignoActivity, $uid) {
        $output = '';
        $scorm_controller = \Drupal::service('opigno_scorm.scorm');
        $e = $opignoActivity->get('opigno_scorm_package');
        foreach ($e->referencedEntities() as $file) {
            $scorm = $scorm_controller->scormLoadByFileEntity($file);

            // Get SCORM API version.
            $metadata = unserialize($scorm->metadata);
            if (strpos($metadata['schemaversion'], '1.2') !== FALSE) {
                $scorm_version = '1.2';
                $completion_key = 'cmi.core.lesson_status';
            } else {
                $scorm_version = '2004';
                $completion_key = 'cmi.completion_status';
            }

            $completion_status = opigno_scorm_scorm_cmi_get($uid,  $scorm->id, $completion_key);

            $output .= 'Completion status: '. $completion_status;
        }

        return $output;
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
            $this->opignoScormPlayerScormTree($scorm);

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

    /**
     * @param $scorm
     * @param int $parent_identifier
     * @return array
     * @see OpignoScormPlayer::opignoScormPlayerScormTree()
     */
    protected function opignoScormPlayerScormTree($scorm, $parent_identifier = 0) {
        $tree = [];

        $result = db_select('opigno_scorm_package_scos', 'sco')
            ->fields('sco', array('id'))
            ->condition('sco.scorm_id', $scorm->id)
            ->condition('sco.parent_identifier', $parent_identifier)
            ->execute();

        while ($sco_id = $result->fetchField()) {
            $sco = $this->scormLoadSco($sco_id);

            $children = $this->opignoScormPlayerScormTree($scorm, $sco->identifier);

            $sco->children = $children;

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