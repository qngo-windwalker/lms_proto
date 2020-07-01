<?

namespace Drupal\wind_lms;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;

class WindLMSSCORMAPI {

  function getSCORMContent(\Drupal\user\Entity\User $user) {
    /** @var \Drupal\group\GroupMembershipLoader $grp_membership_service */
    $grp_membership_service = \Drupal::service('group.membership_loader');
    $uid = $user->id();
    $grps = $grp_membership_service->loadByUser($user);
    foreach ($grps as $grp) {
      $group = $grp->getGroup();
      $gid = $group->id();
    }

    if (!isset($gid)) {
      return [
        '#type' => 'markup',
        '#markup' => 'There are no courses enrolled to you at this time.'
      ];
    }

    $group = Group::load($gid);
    $title = $group->label();

    //    $this->getScormScore($uid, $gid);
    // @see \Drupal\opigno_learning_path\Controller\LearningPathStepsController::start().
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
    // Each step is an opigno_module.
    $steps = array_map(function ($step) {
      return [
        $this->buildCourseLink($step),
        //        $step['name'],
        //        $this->buildScore($step),
        $this->buildState($step),
      ];
    }, $steps);

    $summary = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_steps_block_summary'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => [$state_class],
        ],
        '#value' => '',
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_title'],
        ],
        '#value' => $state_title,
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_score'],
        ],
        '#value' => t('Average score : @score%', [
          '@score' => $score,
        ]),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_progress'],
        ],
        '#value' => t('Progress : @progress%', [
          '@progress' => $progress,
        ]),
      ],
    ];
    //
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_steps_block'],
      ],
      $summary,
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $title,
        '#attributes' => [
          'class' => ['lp_steps_block_title'],
        ],
      ],
      [
        '#type' => 'table',
        '#header' => [
          t('Name'),
          //          t('Score'),
          t('State'),
        ],
        '#rows' => $steps,
        '#attributes' => [
          'class' => ['lp_steps_block_table'],
        ],
      ],
      '#attached' => [
        'library' => [
          'opigno_learning_path/steps_block',
          'wind/learner_dashboard',
        ],
      ],
    ];
  }
}
