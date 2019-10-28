<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;

class WindLMSCourseUserRemoveForm extends FormBase{

  /**
   * @var \Drupal\group\Entity\Group $group
   */
  private $group;
  private $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_course_user_remove';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL, User $user = NULL) {
    $this->group = $group;
    $this->user = $user;
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;
    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mr-1']
      )
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Remove User',
      '#attributes' => array(
        'class' => ['btn', 'btn-danger']
      )
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->group->removeMember($this->user);
    $destination = \Drupal::request()->query->get('destination');
    if ($destination) {
      $form_state->setRedirectUrl(Url::fromUri('internal:' . $destination));
    } else {
      $form_state->setRedirect('<front>');
    }
    $messenger = \Drupal::messenger();
    $messenger->addMessage(t('Successful removing %user from %label.', [
      '%user' => $this->user->getUsername(),
      '%label' => $this->group->label(),
    ]), $messenger::TYPE_STATUS);
  }

  public function getTitle(Group $group, User $user) {
    return $this->t('Are you sure you want to remove %user from %label?', [
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
}
