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
use Drupal\user\UserInterface;

class WindLMSAdminLearnerUserController extends ControllerBase {

    public function getContent(UserInterface $user) {
        module_load_include('inc', 'wind_lms', 'inc/wind_lms.admin');
        $output = '';
        $trainings = array();

        /** @var \Drupal\group\GroupMembershipLoader $grp_membership_service */
        $grp_membership_service = \Drupal::service('group.membership_loader');
        $grps = $grp_membership_service->loadByUser($user);
        foreach ($grps as $grp) {
            $group = $grp->getGroup();
            $gid = $group->id();
            $group = Group::load($gid);

            $output .= $this->getUserGroupSteps($user, $group);
            $trainings[$gid] = array(
                Link::fromTextAndUrl($group->label(), Url::fromUri("internal:/admin/people/learner-score/{$user->id()}/group/{$gid}"))->toString(),
                Link::fromTextAndUrl($gid, Url::fromUri("internal:/group/{$gid}"))->toString(),
                'step_mod' => array('data' => array('#markup' => $output)),
            );
        }

        $headers = [
            [
                'data' => $this->t('Group/Learning Path'),
            ],
            [
                'data' => 'Group ID',
            ],
            [
                'data' => $this->t('Step/Module'),
            ]
        ];

        return ['group_list' =>
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
                '#rows' => $trainings,
            )
        ];
    }

    /**
     * Title callback for the wind_lms.admin_people.user route.
     */
    public function getTitle(UserInterface $user) {
        return $user->getUsername();
    }

    private function getUserGroupSteps($user, $group) {
        $uid = $user->id();
        $gid = $group->id();
        $group_steps = opigno_learning_path_get_steps($gid, $uid);
        $steps = [];
        $stepRows = array();

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
//            $output .= $this->getStepCompletedOn($step);
//            $activityRows = $this->getStepActivityRows($step, $user);
            $links = Link::fromTextAndUrl('View Activities', $url)->toString();
            $links .= '<br />' . Link::fromTextAndUrl('More Info', $url = Url::fromRoute(
                    'wind_lms.admin_people.user_group_step',
                    array(
                        'user' => $uid,
                        'group' => $gid,
                        'opigno_module' => $step['id'],
                    )
                ))->toString();

            $stepRows[$index] = array(
                'data' => [
                    'title' => Link::fromTextAndUrl($step['name'], $url)->toString(),
                    'id' => $step['id'],
                    'status' => '',
                    'attemps' => $step['attempts'],
                    'type' => $step['typology'],
                    'links' => array( 'data' => array('#markup' => $links))
                ]
            );
        }

        $renderable =  [
            '#type' => 'table',
            '#header' => [
                t('Step'),
                t('Id'),
                t('Status'),
                t('Attempts'),
                t('Type'),
                t('Links'),
                t('Max Score'),
                t('Data'),
                t('SCORM Score'),
            ],
            '#rows' => $stepRows,
            '#attributes' => [
                'class' => ['step_block_table'],
            ],
        ];

        return render($renderable);
    }
}