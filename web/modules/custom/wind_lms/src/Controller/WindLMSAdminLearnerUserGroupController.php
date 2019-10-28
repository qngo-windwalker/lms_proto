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

class WindLMSAdminLearnerUserGroupController extends ControllerBase {

    public function getContent(UserInterface $user, $group) {
        module_load_include('inc', 'wind_lms', 'inc/wind_lms.admin');
        $gid = $group->id();
        $title = $group->label();
        $l = Link::fromTextAndUrl(
            $title . " - GID :  {$gid}",
            Url::fromUri("internal:/group/{$gid}")
        );

        $output = '';
        $renderable =  [
            '#type' => 'table',
            '#header' => [
                t('Steps/Modules'),
                t('CID'),
                t('Id'),
                t('Type'),
                t('Time Spent'),
                t('Completed On'),
                t('Best Score'),
                t('Required Score'),
                t('Attempts'),
                t('Activities'),
                t('Mandatory'),
            ],
            '#rows' => $this->getUserGroupSteps($user, $group),
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

    public function getTitle(UserInterface $user, $group) {
        return $group->label();
    }

    private function getUserGroupSteps($user, $group) {
        $uid = $user->id();
        $gid = $group->id();
        $group_steps = opigno_learning_path_get_steps($gid, $uid);
        $steps = [];
        $stepCollections = array();

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
//            $output .= $this->getStepCompletedOn($step);
//            $activityRows = $this->getStepActivityRows($step, $user);

            $stepCollections[$step['id']] = array(
                $stepLink,
                $step['cid'],
                $step['id'],
                $step['typology'],
                $step['time spend'],
                date('Y-m-d', $step['completed on']),
                $step['best score'],
                $step['required score'],
                $step['attempts'],
                $step['activites'],
                $step['mandatory'],


            );
        }

        return $stepCollections;
    }
}