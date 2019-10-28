<?php

namespace Drupal\wind_lms\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\Core\Controller\ControllerBase;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;

/**
 * Provides a 'My Training' block.
 *
 * @Block(
 *  id = "wind_lms_my_training",
 *  admin_label = @Translation("Wind LMS My Training Block"),
 * )
 */
class WindLMSMyTrainingBlock extends Blockbase{

  /**
   * {@inheritdoc}
   */
  public function build() {
    $title = isset($this->configuration['title']) ? $this->configuration['title'] : 'My Training';
    $build = wind_gen_block_card_template($title);

    $user = \Drupal::currentUser();
    $uid = $user->id();
    $grp_membership_service = \Drupal::service('group.membership_loader');
    $grps = $grp_membership_service->loadByUser($user);
    foreach ($grps as $grp) {
      /** @var \Drupal\group\Entity\Group $group */
      $group = $grp->getGroup();
      if ($group->getGroupType()->label() == 'Learning Path') {
        $gid = $group->id();
      }
    }

    if (!isset($gid)) {
      $build['card_body']['card_text_container']['#markup'] = 'There are no courses assigned to you.';
      return $build;
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
    $emptyClass = empty($steps) ? 'empty-table' : '';
    $trainingBuild = [
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
          'class' => ['lp_steps_block_table', $emptyClass],
        ],
        '#empty' => t('No data')
      ],
      '#attached' => [
        'library' => [
          'opigno_learning_path/steps_block',
          'wind/learner_dashboard',
        ],
      ],
    ];


    $build['card_body']['card_text_container']['#markup'] = render($trainingBuild);

    return $build;
  }

  protected function blockAccess(AccountInterface $account) {
    return parent::blockAccess($account); // TODO: Change the autogenerated stub
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $formState) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $formState) {
    $this->configuration['my_block_settings'] = $formState->getValues('my_block_settings');

    return $form;
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
      'wind_lms.answer_form',
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
      'wind_lms.answer_form',
      array('opigno_module' => $moduleId, 'opigno_activity' => $activity->id()),
      array('attributes' => array('target' => '_blank', 'class' => 'wind-scorm-popup-link'))
    );
    return Link::fromTextAndUrl($activity->get('name')->value, $url)->toString();
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

}
