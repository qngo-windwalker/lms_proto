<?php

namespace Drupal\opigno_learning_path\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_module\Entity\OpignoModule;

class LearningPathAchievementController extends ControllerBase {

  /**
   * Returns max score that user can have in this module & activity.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModule $module
   * @param \Drupal\opigno_module\Entity\OpignoActivity $activity
   *
   * @return int
   */
  protected function get_activity_max_score($module, $activity) {
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $query = $db_connection->select('opigno_module_relationship', 'omr')
      ->fields('omr', ['max_score'])
      ->condition('omr.parent_id', $module->id())
      ->condition('omr.parent_vid', $module->getRevisionId())
      ->condition('omr.child_id', $activity->id())
      ->condition('omr.child_vid', $activity->getRevisionId())
      ->condition('omr.activity_status', 1);
    $results = $query->execute()->fetchAll();

    if (empty($results)) {
      return 0;
    }

    $result = reset($results);
    return $result->max_score;
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_name($step) {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_step_name'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['lp_step_name_title'],
        ],
        '#value' => $step['name'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['lp_step_name_activities'],
        ],
        '#value' => ' &dash; ' . $step['activities'] . ' Activities',
      ],
    ];
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_score($step) {
    $uid = $this->currentUser()->id();

    if (opigno_learning_path_is_attempted($step, $uid)) {
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

    return ['#markup' => '&nbsp;'];
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_state($step) {
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
  protected function build_module_panel($step) {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    $user = $this->currentUser();

    $completed_on = $step['completed on'];
    $completed_on = $completed_on > 0
      ? $date_formatter->format($completed_on, 'custom', 'F d, Y')
      : '';

    /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
    $module = OpignoModule::load($step['id']);
    /** @var \Drupal\opigno_module\Entity\UserModuleStatus[] $attempts */
    $attempts = $module->getModuleAttempts($user);

    $activities = $module->getModuleActivities();
    /** @var \Drupal\opigno_module\Entity\OpignoActivity[] $activities */
    $activities = array_map(function ($activity) {
      /** @var \Drupal\opigno_module\Entity\OpignoActivity $activity */
      return OpignoActivity::load($activity->id);
    }, $activities);

    if (!empty($attempts)) {
      usort($attempts, function ($a, $b) {
        /** @var \Drupal\opigno_module\Entity\UserModuleStatus $a */
        /** @var \Drupal\opigno_module\Entity\UserModuleStatus $b */
        $b_score = opigno_learning_path_get_attempt_score($b);
        $a_score = opigno_learning_path_get_attempt_score($a);
        return $b_score - $a_score;
      });

      $best_attempt = reset($attempts);
      $max_score = $best_attempt->calculateMaxScore();
      $score_percent = opigno_learning_path_get_attempt_score($best_attempt);
      $score = round($score_percent * $max_score / 100);
    }
    else {
      $best_attempt = NULL;
      $max_score = !empty($activities)
        ? array_sum(array_map(function ($activity) use ($module) {
          return (int) $this->get_activity_max_score($module, $activity);
        }, $activities))
        : 0;
      $score_percent = 0;
      $score = 0;
    }

    $activities = array_map(function ($activity) use ($user, $module, $best_attempt) {
      /** @var \Drupal\opigno_module\Entity\OpignoActivity $activity */
      /** @var \Drupal\opigno_module\Entity\OpignoAnswer $answer */
      $answer = isset($best_attempt)
        ? $activity->getUserAnswer($module, $best_attempt, $user)
        : NULL;
      $score = isset($answer) ? $answer->getScore() : 0;
      $max_score = (int) $this->get_activity_max_score($module, $activity);

      return [
        ['data' => $activity->getName()],
        [
          'data' => [
            '#markup' => $score . '/' . $max_score,
          ],
        ],
        [
          'data' => [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#attributes' => [
              'class' => [$score >= $max_score
                ? 'lp_step_state_passed'
                : 'lp_step_state_failed'],
            ],
            '#value' => '',
          ],
        ],
      ];
    }, $activities);

    $activities = [
      '#type' => 'table',
      '#attributes' => [
        'class' => ['lp_module_panel_activities_overview'],
      ],
      '#rows' => $activities,
    ];

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_module_panel'],
        'data-module-id' => $step['id'],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_module_panel_header'],
        ],
        [
          '#markup' => '<a href="#" class="lp_module_panel_close">&times;</a>',
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#attributes' => [
            'class' => ['lp_module_panel_title'],
          ],
          '#value' => $step['name'] . ' '
            . (!empty($completed_on)
              ? t('completed')
              : ''),
        ],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'hr',
        '#value' => '',
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_module_panel_content'],
        ],
        (!empty($completed_on)
          ? [
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => t('@name completed on @date', [
              '@name' => $step['name'],
              '@date' => $completed_on,
            ]),
          ]
          : []),
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => t('User got @score of @max_score possible points.', [
            '@score' => $score,
            '@max_score' => $max_score,
          ]),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => t('Total score @percent%', [
            '@percent' => $score_percent,
          ]),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#attributes' => [
            'class' => ['lp_module_panel_overview_title'],
          ],
          '#value' => t('Activities Overview'),
        ],
        $activities,
        (isset($best_attempt)
          ? [
            Link::createFromRoute('Details', 'opigno_module.module_result', [
              'opigno_module' => $module->id(),
              'user_module_status' => $best_attempt->id(),
            ])->toRenderable(),
          ]
          : []),
      ],
    ];
  }

  /**
   * @param array $course
   *
   * @return array
   */
  protected function build_course_steps($course) {
    $user = $this->currentUser();
    $steps = opigno_learning_path_get_steps($course['id'], $user->id());
    $rows = array_map(function ($step) use ($user) {
      return [
        'data-module-id' => $step['id'],
        'data' => [
          ['data' => $this->build_step_name($step)],
          ['data' => $this->build_step_score($step)],
          ['data' => $this->build_step_state($step)],
        ],
      ];
    }, $steps);

    $modules = array_filter($steps, function ($step) {
      return $step['typology'] === 'Module';
    });
    $module_panels = array_map([$this, 'build_module_panel'], $modules);

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_course_steps_wrapper'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h4',
        '#attributes' => [
          'class' => ['lp_course_steps_title'],
        ],
        '#value' => t('Course Content'),
      ],
      [
        '#type' => 'table',
        '#attributes' => [
          'class' => ['lp_course_steps'],
        ],
        '#header' => [
          t('Module'),
          t('Results'),
          t('State'),
        ],
        '#rows' => $rows,
      ],
      $module_panels,
    ];
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $lp
   *
   * @return array
   */
  protected function build_lp_steps($lp) {
    $user = $this->currentUser();
    $steps = opigno_learning_path_get_steps($lp->id(), $user->id());

    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_details'],
      ],
      array_map(function ($step) use ($user, $date_formatter) {
        $is_module = $step['typology'] === 'Module';
        $is_course = $step['typology'] === 'Course';

        if ($is_module) {
          $activities = opigno_learning_path_get_module_activities($step['id'], $user->id());
        }
        elseif ($is_course) {
          $activities = opigno_learning_path_get_activities($step['id'], $user->id());
        }
        else {
          $activities = [];
        }

        $total = count($activities);
        $attempted = count(array_filter($activities, function ($activity) {
          return $activity['answers'] > 0;
        }));

        $progress = $total > 0
          ? $attempted / $total
          : 0;

        if ($progress < 1) {
          $summary = '<span class="lp_summary_step_state_in_progress"></span>'
            . '<span class="lp_step_summary_title">' . t('Pending') . '</span>';
        }
        else {
          $score = $step['best score'];
          $min_score = $step['required score'];

          if ($score < $min_score) {
            $summary = '<span class="lp_summary_step_state_failed"></span>'
              . '<span class="lp_step_summary_title">' . t('Failed') . '</span>';
          }
          else {
            $summary = '<span class="lp_summary_step_state_passed"></span>'
              . '<span class="lp_step_summary_title">' . t('Passed') . '</span>';
          }
        }

        if ($is_module) {
          $summary .= t('@progress% completion', [
            '@progress' => round(100 * $progress),
          ]);
        }
        elseif ($is_course) {
          $summary .= t('@completion% completion', [
            '@completion' => round(100 * $progress),
          ]);
        }

        $summary .= '<br/>' . t('@score% score', [
          '@score' => $step['best score'],
        ]);

        $summary = ['#markup' => $summary];

        if (opigno_learning_path_is_attempted($step, $user->id())) {
          $rows = [
            [
              'data' => $step['time spent'] > 0
                ? $date_formatter->formatInterval($step['time spent'])
                : ['#markup' => '&dash;'],
            ],
            [
              'data' => $step['completed on'] > 0
                ? $date_formatter->format($step['completed on'], 'custom', 'F d Y')
                : ['#markup' => '&dash;'],
            ],
            '',
          ];
        }
        else {
          $rows = [
            [
              'data' => ['#markup' => '&dash;'],
            ],
            [
              'data' => ['#markup' => '&dash;'],
            ],
            '',
          ];
        }

        $content = [
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
              'class' => array_merge(['lp_step_content'], $is_module
                ? ['lp_step_content_module']
                : []),
            ],
            [
              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_summary_wrapper'],
              ],
              [
                '#type' => 'container',
                '#attributes' => [
                  'class' => ['lp_step_summary'],
                ],
                [
                  '#type' => 'table',
                  '#attributes' => [
                    'class' => ['lp_step_summary_table'],
                  ],
                  '#header' => [
                    t('Time spent'),
                    t('Completed on'),
                    t('Badges earned'),
                  ],
                  '#rows' => [$rows],
                ],
                [
                  '#type' => 'container',
                  '#attributes' => [
                    'class' => ['lp_step_summary_text'],
                  ],
                  $summary,
                ],
                ($is_module
                  ? [
                    '#type' => 'container',
                    '#attributes' => [
                      'class' => ['lp_step_summary_clickable'],
                      'data-module-id' => $is_module ? $step['id'] : 0,
                    ],
                    [
                      '#type' => 'html_tag',
                      '#tag' => 'span',
                      '#attributes' => [
                        'class' => ['lp_step_summary_clickable_title'],
                      ],
                      '#value' => $step['name'] . ' &dash; ' . $step['activities'] . ' Activities',
                    ],
                  ]
                  : []),
              ],
              ($is_module ? $this->build_module_panel($step) : []),
            ],
            ($is_course ? $this->build_course_steps($step) : []),
          ],
        ];

        return $content;
      }, $steps),
    ];
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $lp
   *
   * @return array
   */
  protected function build_lp_timeline($lp) {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = \Drupal::service('date.formatter');
    $user = $this->currentUser();
    $steps = opigno_learning_path_get_steps($lp->id(), $user->id());
    $steps = array_filter($steps, function ($step) {
      return $step['mandatory'];
    });

    $timeline = [];
    $timeline[] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => [
        'class' => ['lp_timeline_begin'],
      ],
      '#value' => '',
    ];

    if (opigno_learning_path_is_attempted($lp, $user->id())) {
      foreach ($steps as $step) {
        $completed_on = $step['completed on'] > 0
          ? $date_formatter->format($step['completed on'], 'custom', 'F d, Y')
          : '';

        $timeline[] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              opigno_learning_path_is_passed($step, $user->id())
                ? 'lp_timeline_step_checked'
                : 'lp_timeline_step',
            ],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['lp_timeline_step_label'],
            ],
            [
              '#type' => 'html_tag',
              '#tag' => 'span',
              '#attributes' => [
                'class' => ['lp_timeline_step_label_title'],
              ],
              '#value' => $step['name'],
            ],
            [
              '#type' => 'html_tag',
              '#tag' => 'span',
              '#attributes' => [
                'class' => ['lp_timeline_step_label_completed_on'],
              ],
              '#value' => $completed_on,
            ],
          ],
        ];
      }
    }

    $timeline[] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => [
        'class' => ['lp_timeline_end'],
      ],
      '#value' => '',
    ];

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_timeline_wrapper', 'px-3', 'px-md-5', 'pb-5'],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'lp_timeline',
            opigno_learning_path_is_attempted($lp, $user->id())
              ? 'lp_timeline_not_empty'
              : 'lp_timeline_empty',
          ],
        ],
        $timeline,
      ],
    ];
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $lp
   *
   * @return array
   */
  protected function build_lp_summary($lp) {
    $id = $lp->id();

    $user = $this->currentUser();
    $uid = $user->id();

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

    /** @var \Drupal\group\Entity\GroupContent $member */
    $member = $lp->getMember($user)->getGroupContent();
    $registration = $member->getCreatedTime();
    $registration = $date_formatter->format($registration, 'custom', 'F d, Y');

    $validation = opigno_learning_path_completed_on($id, $uid);
    $validation = $validation > 0
      ? $date_formatter->format($validation, 'custom', 'F d, Y')
      : '';

    $time_spent = array_sum(array_map(function ($step) {
      return $step['time spent'];
    }, $steps));
    $time_spent = $date_formatter->formatInterval($time_spent);

    $is_attempted = opigno_learning_path_is_attempted($lp, $uid);
    $is_passed = opigno_learning_path_is_passed($lp, $uid);

    if ($is_passed || $is_attempted) {
      $summary = [
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_step_summary_text'],
          ],
          '#value' => t('Score: @score%', [
            '@score' => $score,
          ]),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_step_summary_text'],
          ],
          '#value' => t('Registration date: @date', [
            '@date' => $registration,
          ]),
        ],
        ($is_passed
          ? [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#attributes' => [
              'class' => ['lp_step_summary_text'],
            ],
            '#value' => t('Validation date: @date', [
              '@date' => $validation,
            ]),
          ]
          : []),
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_step_summary_text'],
          ],
          '#value' => t('Time spent: @time on the training', [
            '@time' => $time_spent,
          ]),
        ],
      ];
    }
    else {
      $summary = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['lp_step_summary_text'],
        ],
        '#value' => t('Not yet started'),
      ];
    }

    if ($is_passed) {
      $state_class = 'lp_summary_step_state_passed';
    }
    elseif ($is_attempted) {
      $state_class = 'lp_summary_step_state_in_progress';
    }
    else {
      $state_class = 'lp_summary_step_state_not_started';
    }

    $gid = $lp->id();
    $cert_text = $this->t('Download certificate');
    $has_cert = !$lp->get('field_certificate')->isEmpty();

    if ($is_passed && $has_cert) {
      $cert_url = Url::fromUri("internal:/certificate/group/$gid/pdf");

      $cert_title = Link::fromTextAndUrl($cert_text, $cert_url)->toRenderable();
      $cert_title['#attributes']['class'][] = 'lp_summary_certificate_text';

      $cert_icon = Link::fromTextAndUrl('', $cert_url)->toRenderable();
      $cert_icon['#attributes']['class'][] = 'lp_summary_certificate_icon';
    }
    else {
      $cert_title = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['lp_summary_certificate_text'],
        ],
        '#value' => $cert_text,
      ];
      $cert_icon = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['lp_summary_certificate_icon'],
        ],
        '#value' => '',
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_summary', 'py-5', 'pr-3', 'pr-md-5'],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => array_merge(['lp_summary_certificate'],
            $is_passed && $has_cert ? [] : ['lp_summary_certificate_inactive']),
        ],
        $cert_title,
        $cert_icon,
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_summary_content'],
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
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_step_summary_title'],
          ],
          '#value' => t('Training Progress'),
        ],
        $summary,
      ],
    ];
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $lp
   *
   * @return array
   */
  protected function build_lp($lp) {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_wrapper'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#attributes' => [
          'class' => ['lp_title', 'px-3', 'px-md-5', 'pt-5', 'pb-4', 'mb-0', 'h4', 'text-uppercase'],
        ],
        '#value' => t('Learning Path : @name', [
          '@name' => $lp->label(),
        ]),
      ],
      $this->build_lp_timeline($lp),
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_timeline_info', 'px-3', 'px-md-5', 'py-3'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_timeline_icon'],
          ],
          '#value' => '',
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_timeline_info_text'],
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#value' => t('Timeline'),
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#attributes' => [
              'class' => ['lp_timeline_info_tooltip'],
            ],
            '#value' => t('In your timeline are shown only successfully passed mandatory steps from your Learning Path'),
          ],
        ],
      ],
      $this->build_lp_summary($lp),
      $this->build_lp_steps($lp),
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_details_show'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_details_show_text'],
          ],
          '#value' => t('Show details'),
        ],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_details_hide'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_details_hide_text'],
          ],
          '#value' => t('Hide details'),
        ],
      ],
    ];
  }

  /**
   * @return array
   */
  public function index() {
    $user = $this->currentUser();

    // Get all learning paths.
    $query = \Drupal::entityQuery('group');
    $query->condition('type', 'learning_path');
    $lp_ids = $query->execute();
    $lps = Group::loadMultiple($lp_ids);

    // Filter learning paths that have current user as member.
    $lps = array_filter($lps, function ($lp) use ($user) {
      /** @var \Drupal\group\Entity\GroupInterface $lp */
      return $lp->getMember($user) !== FALSE;
    });

    // Sort learning paths by completion date.
    usort($lps, function ($lp1, $lp2) use ($user) {
      $uid = $user->id();

      /** @var \Drupal\group\Entity\GroupInterface $lp1 */
      /** @var \Drupal\group\Entity\GroupInterface $lp2 */
      $completed1 = opigno_learning_path_completed_on($lp1->id(), $uid);
      $completed2 = opigno_learning_path_completed_on($lp2->id(), $uid);

      // If both not completed, show attempted first.
      if ($completed1 === 0 && $completed2 === 0) {
        $attempted1 = opigno_learning_path_is_attempted($lp1, $uid) ? 1 : 0;
        $attempted2 = opigno_learning_path_is_attempted($lp2, $uid) ? 1 : 0;

        return $attempted2 - $attempted1;
      }

      return $completed2 - $completed1;
    });

    $content = [
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_info', 'mb-4', 'py-4', 'pr-3', 'pr-md-5'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => ['lp_icon_info'],
          ],
          '#value' => '',
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_info_text'],
          ],
          '#value' => t('Consult your results and download the certificates for the learning paths.'),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_info_text'],
          ],
          '#value' => t('Only the highest are displayed'),
        ],
      ],
      array_map([$this, 'build_lp'], $lps),
      '#attached' => [
        'library' => [
          'opigno_learning_path/achievements',
        ],
      ],
    ];

    return $content;
  }

  /**
   * Check the access for the achievements page.
   */
  public function access(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
