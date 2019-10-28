<?php

namespace Drupal\wind_lms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'WindLMSGroupMembershipBlock' block.
 *
 * @Block(
 *  id = "wind_lms_group_membership_block",
 *  admin_label = @Translation("Membership"),
 * )
 */
class WindLMSGroupMembershipBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $currentUser = \Drupal::currentUser();
    $org = ch_nav_get_user_group_organization($currentUser);
    if ($org) {
      $markup = $this->getUsersTable($org);
      $markup .= $this->genAddUserLink($org->id()) . $this->genManageUserLink($org->id()) ;
    } else {
      $markup = '<p>Your account does not belong to any organization.</p>';
    }
    $renderable = [
      '#theme' => 'wind_bootstrap_block_card',
      '#label' => 'Users',
      '#content' => $markup,
    ];
    return $renderable;
  }

  private function getUsersTable(Group $group) {
    $rows = array();
    $groupMembers = $group->getMembers();
    foreach ($groupMembers as $groupMember) {
      $groupContent = $groupMember->getGroupContent();
      $user = $groupMember->getUser();
      $uid = $user->id();
      $firstName = $user->get('field_first_name')->getString();
      $lastName = $user->get('field_last_name')->getString();
      $role = $groupContent->get('group_roles')->getSTring();
      $roleLabel = $role ? \Drupal\group\Entity\GroupRole::load($role)->label() : 'Unassigned';
      $rows[$uid] = array(
        'data' => [
          array('data' => "{$firstName}  {$lastName}",  'class' => array('course-full-name')),
          $user->getEmail(),
          array('data' => $roleLabel,  'class' => array('group-role')),
        ],
        'class' => array('uid-'. $uid),
      );
    }

    $header = [
      array('data' => 'Name', 'class' => 'user-full-name-header'),
      array('data' => 'Email', 'class' => 'user-email-header'),
      array('data' => 'Role', 'class' => 'user-group-role-header'),
    ];
    $renderable = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
    ];
    return \Drupal::service('renderer')->render($renderable);
  }

  private function genAddUserLink($gid) {
    $url = Url::fromRoute(
      'ch_nav.org.adduser',
      array('group' => $gid),
      array('attributes' => array('class' => 'card-link'))
    );
    $linkContent = '<i class="fas fa-plus-circle"></i> Add User';
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  private function genManageUserLink($gid) {
    $url = Url::fromRoute(
      'ch_nav.org',
      array('group' => $gid),
      array('attributes' => array('class' => 'card-link'))
    );
    $linkContent = '<i class="fas fa-users"></i> Manage Users';
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }
}
