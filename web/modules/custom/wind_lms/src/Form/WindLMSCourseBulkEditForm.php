<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;

class WindLMSCourseBulkEditForm extends FormBase{
  private $group;
  private $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_course_bulk_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Node $node = NULL) {

    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;

    $form['enrollment']['enrollment_options'] = [
      '#title' => t('Enroll/Un-enroll to course'),
      '#type' => 'radios',
      '#options' => array(
        'enroll_user' => 'Enroll User(s)',
        'unenroll_user' => 'Un-Enroll User(s)',
        'enroll_all_users' => 'Enroll Everyone In The System',
        'unenroll_all_users' => 'Un-Enroll Everyone In The System',
        'enroll_team' => 'Enroll Team',
        'unenroll_team' => 'Un-enroll Team'
      ),
    ];

    $form['enrollment']['user'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="enrollment[enrollment_options]"]' => [
            array('value' => 'enroll_user'),
            array('value' => 'unenroll_user')
          ],
        ],
      ],
      '#attributes' => array('class' => ['ml-5'])
    ];

    $form['enrollment']['user']['user_select'] = [
      '#type' => 'select',
      '#title' => t('Select User(s)'),
      '#multiple' => TRUE,
      '#options' => $this->getEnrollmentTeamOptions(),
    ];

    $form['enrollment']['team'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="enrollment[enrollment_options]"]' => [
            array('value' => 'enroll_team'),
            array('value' => 'unenroll_team')
          ],
        ],
      ],
      '#attributes' => array('class' => ['ml-5'])
    ];

    $form['enrollment']['team']['team_select'] = [
      '#type' => 'select',
      '#title' => t('Select Team'),
      '#multiple' => TRUE,
      '#options' => $this->getEnrollmentTeamOptions(),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Enroll User',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary', 'mr-3', 'mt-3']
      )
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mr-1', 'mt-3']
      )
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->group->addMember($this->user, array('group_roles' => array('learning_path-student')));

    $messenger = \Drupal::messenger();
    $messenger->addMessage(t('Successful adding %user to %label.', [
      '%user' => $this->user->getUsername(),
      '%label' => $this->group->label(),
    ]), $messenger::TYPE_STATUS);

    $destination = \Drupal::request()->query->get('destination');
    if ($destination) {
      //      $form_state->setRedirectUrl(Url::fromUri('internal:' . $destination));
      $form_state->setRedirectUrl(Url::fromUserInput($destination));
    } else {
      $form_state->setRedirect('<front>');
    }
  }

  public function getTitle(Group $group, User $user) {
    return $this->t('Are you sure you want to enroll %user to %label?', [
      '%user' => $user->getUsername(),
      '%label' => $group->label(),
    ]);
  }

  public function access( Group $group, User $user) {
    $currentUser = \Drupal::currentUser();
    // Commented out for reminder to enforce real world scenario.
    // Admin user can add add user using /admin/group pages.
    //    if (wind_does_user_has_sudo($currentUser)){
    //      return AccessResult::allowed();
    //    }

    // Make sure the current user only allow to make edit to users that are in the same group,
    // except for the 'Learning Path' group
    $currentUserSameGroups = wind_lms_current_user_and_target_user_in_groups($currentUser, $user, $group->getGroupType()->label());
    if(empty($currentUserSameGroups)){
      return AccessResult::forbidden();
    }

    // Check if the current user has the right role or permissions.
    foreach ($currentUserSameGroups as $currentUserSameGroup) {
      $roles = wind_lms_get_user_group_roles($currentUser, $currentUserSameGroup);
      foreach ($roles as $role) {
        if ($role->label() == 'Admin') {
          return AccessResult::allowed();
        }
      }
    }
    return AccessResult::forbidden();
  }

  private function getEnrollmentTeamOptions() {
    $vid = 'user_team';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $options = array();
    foreach ($terms as $term) {
      $options[ $term->tid] =  $term->name;
    }
    return $options;
  }

}
