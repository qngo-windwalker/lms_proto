<?php

namespace Drupal\opigno_learning_path\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\forum\Controller\ForumController;
use Drupal\taxonomy\Entity\Term;
use Drupal\tft\Controller\TFTController;

class LearningPathController extends ControllerBase {

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_score_cell($step) {
    if ($step['typology'] === 'Module' || $step['typology'] === 'Course') {
      $score = $step['best score'];

      return [
        '#type' => 'container',
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $score . '%',
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_result_bar'],
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => ['lp_step_result_bar_value'],
              'style' => "width: $score%",
            ],
            '#value' => '',
          ],
        ],
      ];
    }
    else {
      return ['#markup' => '&dash;'];
    }
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_state_cell($step) {
    $user = $this->currentUser();
    $uid = $user->id();

    if ($step['typology'] === 'Module') {
      $activities = opigno_learning_path_get_module_activities($step['id'], $uid);
    }
    elseif ($step['typology'] === 'Course') {
      $activities = opigno_learning_path_get_activities($step['id'], $uid);
    }
    else {
      return ['#markup' => '&dash;'];
    }

    $total = count($activities);
    $attempted = count(array_filter($activities, function ($activity) {
      return $activity['answers'] > 0;
    }));

    $progress = $total > 0
      ? $attempted / $total
      : 0;

    if ($progress < 1) {
      $state = '<span class="lp_step_state_pending"></span>' . t('Pending');
    }
    else {
      $score = $step['best score'];
      $min_score = $step['required score'];

      if ($score < $min_score) {
        $state = '<span class="lp_step_state_failed"></span>' . t('Failed');
      }
      else {
        $state = '<span class="lp_step_state_passed"></span>' . t('Passed');
      }
    }

    return ['#markup' => $state];
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_course_row($step) {
    $result = $this->build_step_score_cell($step);
    $state = $this->build_step_state_cell($step);

    return [
      $step['name'],
      [
        'class' => 'lp_step_details_result',
        'data' => $result,
      ],
      [
        'class' => 'lp_step_details_state',
        'data' => $state,
      ],
    ];
  }

  /**
   * @return array
   */
  public function progress() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = \Drupal::routeMatch()->getParameter('group');
    $user = \Drupal::currentUser();

    $id = $group->id();
    $uid = $user->id();

    $progress = opigno_learning_path_progress($id, $uid);
    $progress = round(100 * $progress);

    if (opigno_learning_path_is_passed($group, $uid)) {
      $steps = opigno_learning_path_get_steps($id, $uid);
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

      /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
      $date_formatter = \Drupal::service('date.formatter');

      $completed = opigno_learning_path_completed_on($id, $uid);
      $completed = $completed > 0
        ? $date_formatter->format($completed, 'custom', 'F d, Y')
        : '';

      $summary = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_progress_summary'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_passed'],
          ],
          '#value' => '',
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#attributes' => [
            'class' => ['lp_progress_summary_title'],
          ],
          '#value' => t('Passed'),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_score'],
          ],
          '#value' => t('Average score : @score%', [
            '@score' => $score,
          ]),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_date'],
          ],
          '#value' => t('Completed on @date', [
            '@date' => $completed,
          ]),
        ],
      ];
    }

    $content = [];
    $content[] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-sm-9', 'mb-3'],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_progress'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_label'],
          ],
          '#value' => t('Global Training Progress'),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_value'],
          ],
          '#value' => $progress . '%',
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_progress_bar'],
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => ['lp_progress_bar_completed'],
              'style' => "width: $progress%",
            ],
            '#value' => '',
          ],
        ],
      ],
      isset($summary) ? $summary : [],
      '#attached' => [
        'library' => [
          'opigno_learning_path/training_content',
        ],
      ],
    ];

    $continue_route = 'opigno_learning_path.steps.start';
    $edit_route = 'entity.group.edit_form';
    $members_route = 'opigno_learning_path.membership.overview';

    $route_args = ['group' => $group->id()];
    $continue_url = Url::fromRoute($continue_route, $route_args);
    $edit_url = Url::fromRoute($edit_route, $route_args);
    $members_url = Url::fromRoute($members_route, $route_args);

    $admin_continue_button = Link::fromTextAndUrl('', $continue_url)->toRenderable();
    $admin_continue_button['#attributes']['class'][] = 'lp_progress_admin_continue';
    $edit_button = Link::fromTextAndUrl('', $edit_url)->toRenderable();
    $edit_button['#attributes']['class'][] = 'lp_progress_admin_edit';
    $members_button = Link::fromTextAndUrl('', $members_url)->toRenderable();
    $members_button['#attributes']['class'][] = 'lp_progress_admin_edit';

    $continue_button_text = $this->t('Continue Training');
    $continue_button = Link::fromTextAndUrl($continue_button_text, $continue_url)->toRenderable();
    $continue_button['#attributes']['class'][] = 'lp_progress_continue';

    $buttons = [];
    if ($user->hasPermission('manage group content in any group')
      || $group->hasPermission('edit group', $user)) {
      $buttons[] = $admin_continue_button;
      $buttons[] = $edit_button;
    }
    elseif ($user->hasPermission('manage group members in any group')
      || $group->hasPermission('administer members', $user)) {
      $buttons[] = $admin_continue_button;
      $buttons[] = $members_button;
    }
    else {
      $buttons[] = $continue_button;
    }

    $content[] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-sm-3', 'mb-3'],
      ],
      $buttons,
    ];

    return $content;
  }

  /**
   * @return array
   */
  public function trainingContent() {
    /** @var \Drupal\group\Entity\Group $group */
    $group = \Drupal::routeMatch()->getParameter('group');
    $user = \Drupal::currentUser();

    $steps = opigno_learning_path_get_steps($group->id(), $user->id());
    $steps = array_map(function ($step) use ($user) {
      $sub_title = '';
      $score = $this->build_step_score_cell($step);
      $state = $this->build_step_state_cell($step);
      $rows = [];

      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $user->id());
        $sub_title = t('@count Modules', [
          '@count' => count($course_steps),
        ]);

        $rows = array_map([$this, 'build_course_row'], $course_steps);
      }

      return [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_step'],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_title_wrapper'],
          ],
          ($step['mandatory']
            ? [
              '#type' => 'html_tag',
              '#tag' => 'span',
              '#attributes' => [
                'class' => ['lp_step_required'],
              ],
              '#value' => '',
            ]
            : []),
          [
            '#type' => 'html_tag',
            '#tag' => 'h3',
            '#attributes' => [
              'class' => ['lp_step_title'],
            ],
            '#value' => $step['name'],
          ],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_content'],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['lp_step_summary'],
            ],
            [

              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_summary_title_wrapper'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'h3',
                '#attributes' => [
                  'class' => ['lp_step_summary_title'],
                ],
                '#value' => $step['name'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'h4',
                '#attributes' => [
                  'class' => ['lp_step_summary_subtitle'],
                ],
                '#value' => $sub_title,
              ],
            ],
            [
              '#type' => 'table',
              '#attributes' => [
                'class' => ['lp_step_summary_details'],
              ],
              '#header' => [
                t('Score'),
                t('State'),
              ],
              '#rows' => [
                [
                  [
                    'class' => 'lp_step_details_result',
                    'data' => $score,
                  ],
                  [
                    'class' => 'lp_step_details_state',
                    'data' => $state,
                  ],
                ],
              ],
            ],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['lp_step_details_wrapper'],
            ],
            ($step['typology'] === 'Course'
              ? [
                '#type' => 'table',
                '#attributes' => [
                  'class' => ['lp_step_details'],
                ],
                '#header' => [
                  t('Module'),
                  t('Score'),
                  t('State'),
                ],
                '#rows' => $rows,
              ]
              : []),
          ],
        ],
        ($step['typology'] === 'Course'
        ? [
            [
              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_show'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                  'class' => ['lp_step_show_text'],
                ],
                '#value' => t('Show details'),
              ],
            ],
            [
              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_hide'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                  'class' => ['lp_step_hide_text'],
                ],
                '#value' => t('Hide details'),
              ],
            ],
          ] : []),
      ];
    }, $steps);

    $TFTController = new TFTController();
    $listGroup = $TFTController->listGroup($group->id());

    $content = [];
    $content['tabs'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['lp_tabs', 'nav', 'mb-4']],
    ];

    $content['tabs'][] = [
      '#markup' => '<a class="lp_tabs_link active" data-toggle="tab" href="#training-content">' . t('Training Content') . '</a>',
    ];

    $content['tabs'][] = [
      '#markup' => '<a class="lp_tabs_link" data-toggle="tab" href="#documents-library">' . t('Documents Library') . '</a>',
    ];

    $content['tabs'][] = [
      '#markup' => '<a class="lp_tabs_link" data-toggle="tab" href="#collaborative-workspace">' . t('Collaborative Workspace') . '</a>',
    ];

    $content['tab-content'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['tab-content']],
    ];

    $content['tab-content'][] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'training-content', 'class' => ['tab-pane', 'fade', 'show', 'active']],
      'steps' => $steps,
    ];

    $content['tab-content'][] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'documents-library', 'class' => ['tab-pane', 'fade']],
      $listGroup,
    ];

    $content['tab-content'][] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'collaborative-workspace', 'class' => ['tab-pane', 'fade']],
      '#markup' => 'collaborative-workspace',
    ];

    $has_enable_forum_field = $group->hasField('field_learning_path_enable_forum');
    $has_forum_field = $group->hasField('field_learning_path_forum');
    if ($has_enable_forum_field && $has_forum_field) {
      $enable_forum_field = $group->get('field_learning_path_enable_forum')->getValue();
      $forum_field = $group->get('field_learning_path_forum')->getValue();
      if (!empty($enable_forum_field) && !empty($forum_field)) {
        $enable_forum = $enable_forum_field[0]['value'];
        $forum_tid = $forum_field[0]['target_id'];
        if ($enable_forum && _opigno_forum_access($forum_tid, $user)) {
          $forum_term = Term::load($forum_tid);
          $forum_controller = ForumController::create(\Drupal::getContainer());
          $forum = $forum_controller->forumPage($forum_term);

          $content['tabs'][] = [
            '#markup' => '<a class="lp_tabs_link" data-toggle="tab" href="#forum">' . t('Forum') . '</a>',
          ];

          $content['tab-content'][] = [
            '#type' => 'container',
            '#attributes' => [
              'id' => 'forum',
              'class' => ['tab-pane', 'fade'],
            ],
            'forum' => $forum,
          ];
        }
      }
    }

    $content[] = [
      '#attached' => [
        'library' => [
          'opigno_learning_path/training_content',
        ],
      ],
    ];

    return $content;
  }

  /**
   * Check the access for the learning path page.
   */
  public function access(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
