<?php

namespace Drupal\wind_help\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\invite\Entity\Invite;

class ChNavDatatableController extends ControllerBase{
  private $groupLearningPath;
  private $groupOrg;

  /**
   * Callback handler for path: chnav-datatable/course/{group_learning_path}/org/{group_organization}/users
   * @param Group $group_learning_path
   * @param Group $group_organization
   * @return JsonResponse
   */
  public function getAllCourseOrgUsers(Group $group_learning_path, Group $group_organization){
    $this->groupLearningPath = $group_learning_path;
    $this->groupOrg = $group_organization;
    $collection = [];
    $memberData = ch_nav_parse_learning_path_group_vs_org_group($group_learning_path, $group_organization);
    foreach ($memberData['activeUsers'] as $uid => $activeUser) {
      $row = $this->getUserDisplayData($activeUser);
      $row['operations'] = $this->getUserEnrollmentOperations($activeUser, true, $group_learning_path, $group_organization);
      $row['is_enrolled'] = 'Enrolled';
      $collection[] = $row;
    }

    foreach ($memberData['nonActiveUsers'] as $uid => $nonActiveUser) {
      $row = $this->getUserDisplayData($nonActiveUser);
      $row['operations'] = $this->getUserEnrollmentOperations($nonActiveUser, false, $group_learning_path, $group_organization);
      $row['is_enrolled'] = 'Not Enrolled';
      $collection[] = $row;
    }
    return new JsonResponse(['data' => $collection]);
  }

  /**
   * Callback handler path: chnav-datatable/org/{group}/users
   * Also being used on /org/{group}
   * @param Group $group
   * @return JsonResponse
   */
  public function getAllOrgUsers(Group $group) {
    $collection = [];
    $users = ch_nav_get_group_members($group);
    foreach ($users as $uid => $user) {
      $row = $this->getUserDisplayData($user);
      $row['group_role'] = $this->getUserGroupRolesView($user, $group);
      $row['operations'] = $this->getOrgUserOperation($group, $user);
      $collection[] = $row;
    }
    $inviters = ch_nav_get_invited_members($group);
    foreach ($inviters as $invitee) {
      if($invitee->getStatus() == INVITE_USED){
        continue;
      }
      $row = $this->getInvitedUserDisplayData($invitee);
      $row['group_role'] = 'N/A';
      $row['operations'] = $this->getInviteeOperation($invitee, $group);
      $collection[] = $row;
    }

    return new JsonResponse(['data' => $collection]);
  }

  private function formatTime($timestamp) {
    if ($timestamp) {
      return date('m-d-Y', $timestamp);
    } else {
      // If the $timestamp is 0
      return 'Never';
    }
  }

  private function getUserDisplayData(User $user) {
    $uid = $user->id();
    return [
      'status' => $user->isActive() ? 'Active' : 'Inactive',
      'first_name' => $user->get('field_first_name')->getString(),
      'last_name' => $user->get('field_last_name')->getString(),
      'email' => $user->getEmail(),
      'last_login' => $this->formatTime($user->getLastLoginTime()),
      'last_accessed' => $this->formatTime($user->getLastAccessedTime()),
      'rowUid' => 'uid-' . $uid,
      'uid' => $uid,
    ];
  }

  private function getInvitedUserDisplayData(Invite $invitee) {
    $uid = $invitee->getRegCode();
    $inviteStatusLabel = $this->getInviteStatusLabelByCode($invitee->getStatus());
    $owner = $invitee->getOwner();
    $user_id = $invitee->get('user_id')->getString();
    return [
      'status' => $inviteStatusLabel,
      'first_name' => 'N/A',
      'last_name' => 'N/A',
      'email' => $invitee->get('field_invite_email_address')->getString(),
      'last_login' => 'N/A',
      'last_accessed' => 'N/A',
      'rowUid' => 'uid-' . $uid,
      'uid' => $uid,
    ];
  }

  private function renderActiveInput($isEnrolled) {
    $checked = $isEnrolled ? 'checked="checked"' : '';
    return '<input type="checkbox" name="checkboxes[disabled]" disabled="disabled" ' . $checked . ' >';
  }

  private function getUserEnrollmentOperations($user, $isEnrolled, $group_learning_path, $group_organization) {
    if(!$this->currentUserHasOrgPermission(\Drupal::currentUser(), $group_organization, 'enroll user')){
      return 'N/A';
    };
    $destination = Url::fromRoute(
      'ch_nav.course.org',
      ['group_learning_path' => $group_learning_path->id(), 'group_organization' => $group_organization->id() ],
      ['absolute' => FALSE]
    );
    if ($isEnrolled) {
      $url = Url::fromRoute(
        'wind_lms.course.user.remove',
        array('group' => $group_learning_path->id(), 'user' => $user->id()),
        array(
          'query' => ['destination' => $destination->toString()],
          'attributes' => array('class' => 'card-link text-danger')
        )
      );
      $linkContent = '<i class="fas fa-minus-circle "></i> Un-Enroll';
    } else {
      $url = Url::fromRoute(
        'wind_lms.course.user.add',
        array('group' => $group_learning_path->id(), 'user' => $user->id()),
        array(
          'query' => ['destination' => $destination->toString()],
          'attributes' => array('class' => 'card-link')
        )
      );
      $linkContent = '<i class="fas fa-plus-circle"></i> Enroll';
    }
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  private function getUserGroupRolesView($user, $group) {
    $roles = wind_lms_get_user_group_roles($user, $group);
    $roleLabels = array_map(function ($role) {
      return $role->label();
    }, $roles);

    // If user has more than one role, remove the minimum level role
    if (count($roleLabels) > 1) {
      unset($roleLabels['organization-member']);
    }
    return implode('<br />', $roleLabels);
  }

  private function getOrgUserOperation($group, $user) {
    if(!$this->currentUserHasOrgPermission(\Drupal::currentUser(), $group, 'edit user')){
      return 'N/A';
    };
    $url = Url::fromUserInput(
      "/org/{$group->id()}/user/{$user->id()}/edit",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => '')
      )
    );
    $disableUserurl = Url::fromUserInput(
      "/org/{$group->id()}/user/{$user->id()}/disable",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => 'card-link text-danger')
      )
    );
    $linkContent = '<i class="fas fa-pen"></i> Edit';
    $renderedAnchorContent = render($linkContent);
    $removeLinkContent = '<i class="fas fa-minus-circle"></i> Disable';
    $output = Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
    $output .= '<br />' . Link::fromTextAndUrl(Markup::create(render($removeLinkContent)), $disableUserurl)->toString();
    return $output;
  }

  private function currentUserHasOrgPermission($user, $group_organization, $string) {
    // Note! Current user can be outside of current group_organization such as admin.
    if(wind_does_user_has_sudo($user)){
      return true;
    };
    $roles = wind_lms_get_user_group_roles($user, $group_organization);
    if (isset($roles['organization-admin'])) {
      return true;
    }

    return false;
  }

  private function getInviteStatusLabelByCode($code) {
    switch ($code){
      case 1;
//        return 'INVITE_VALID';
        return 'Invited';
        break;
      case 2;
        return 'INVITE_WITHDRAWN';
        break;
      case 3;
        return 'INVITE_USED';
        break;
      case 4;
        return 'INVITE_EXPIRED';
        break;
    }
  }

  private function getInviteeOperation($invitee, $group) {
    if(!$this->currentUserHasOrgPermission(\Drupal::currentUser(), $group, 'edit user')){
      return 'N/A';
    };
    $resendInviteURL = Url::fromUserInput(
      "/org/{$group->id()}/invite/{$invitee->id()}/resend",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => '')
      )
    );
    $withdrawInviteURL = Url::fromUserInput(
      "/org/{$group->id()}/invite/{$invitee->id()}/withdraw",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => 'card-link text-danger')
      )
    );
    $linkContent = '<i class="fas fa-pen"></i> Resend';
    $renderedAnchorContent = render($linkContent);
    $removeLinkContent = '<i class="fas fa-minus-circle"></i> Withdraw';
    $output = Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $resendInviteURL)->toString();
    $output .= '<br />' . Link::fromTextAndUrl(Markup::create(render($removeLinkContent)), $withdrawInviteURL)->toString();
    return $output;
  }
}
