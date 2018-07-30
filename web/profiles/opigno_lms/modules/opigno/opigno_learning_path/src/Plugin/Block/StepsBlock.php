<?php
/**
 * @file
 * Contains \Drupal\opigno_learning_path\Plugin\Block\StepsBlock.
 */

namespace Drupal\opigno_learning_path\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;

/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "lp_steps_block",
 *   admin_label = @Translation("LP Steps block")
 * )
 */
class StepsBlock extends BlockBase {

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
    }
    else {
      $score = ['#markup' => '&dash;'];
    }

    return [
      'data' => $score,
    ];
  }

  protected function buildState($step) {
    $is_attempted = $step['attempts'] > 0;
    $is_passed = $step['best score'] >= $step['required score'];

    if ($is_attempted) {
      if ($is_passed) {
        $state = '<span class="lp_steps_block_step_passed"></span>'
          . $this->t('Passed');
      }
      else {
        $state = '<span class="lp_steps_block_step_failed"></span>'
          . $this->t('Failed');
      }
    }
    else {
      $state = '<span class="lp_steps_block_step_pending"></span>';
    }

    return [
      'data' => [
        '#markup' => $state,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();

    $uid = $user->id();
    $gid = OpignoGroupContext::getCurrentGroupId();
    $cid = OpignoGroupContext::getCurrentGroupContentId();

    if (!isset($gid)) {
      return [];
    }

    $group = Group::load($gid);
    $title = $group->label();

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

    // Calculate score.
    $mandatory_steps = array_filter($steps, function ($step) {
      return $step['mandatory'];
    });
    if (!empty($mandatory_steps)) {
      $score = round(array_sum(array_map(function ($step) {
        return $step['best score'];
      }, $mandatory_steps)) / count($mandatory_steps));
    }
    else {
      $score = 0;
    }

    $progress = opigno_learning_path_progress($gid, $uid);
    $progress = round(100 * $progress);

    $is_passed = opigno_learning_path_is_passed($group, $uid);

    if ($is_passed) {
      $state_class = 'lp_steps_block_summary_state_passed';
      $state_title = $this->t('Passed');
    }
    else {
      $state_class = 'lp_steps_block_summary_state_pending';
      $state_title = $this->t('In progress');
    }

    $steps = array_map(function ($step) {
      return [
        $step['name'],
        $this->buildScore($step),
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
          t('Score'),
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
        ],
      ],
    ];
  }

}
