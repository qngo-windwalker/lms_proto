<?php

namespace Drupal\wind_help\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindHelpSupportTicketCollectionController extends ControllerBase{
  public function getContent() {
    $table = $this->getDataTableRenderable('support-ticket-tbl', "/help/support-ticket-datatable/");
    $markup = '';
    $markup .= render($table);
    return [
      '#markup' => $markup,
    ];
  }

  private function getDataTableRenderable($tableElemntId, $datatableURL) {
    $header = [
      array('data' => 'Ticket ID', 'class' => 'node-first-name-header'),
      array('data' => 'Type', 'class' => 'node-last-name-header'),
      array('data' => 'Status', 'class' => 'node-email-header'),
      array('data' => 'Created On', 'class' => 'node-enrollment-header'),
      array('data' => 'Last Updated', 'class' => 'node-last-login-header'),
      array('data' => 'Operations', 'class' => 'node-operations-header'),
    ];

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => $tableElemntId,
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          'wind_help/support_ticket'
        ),
        'drupalSettings' => array(
          'wind_help' => array(
            'datatableURL' => $datatableURL,
            'datatableElementId' => '#' . $tableElemntId
          )
        )
      )
    ];
  }

  private function getAddUserAndEnrollLink(Group $group_learning_path, Group $group_organization) {
    $url = Url::fromUserInput(
      "/org/{$group_organization->id()}/adduser",
      array(
        'query' => [
          'destination' => Url::fromRoute('<current>')->toString(),
          'autoEnrollGid' => $group_learning_path->id()
        ],
        'attributes' => array('class' => 'btn btn-info')
      )
    );
    $linkContent = '<i class="fas fa-plus-circle"></i> Add User and Enroll';
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  /**
   * Check the access to this form.
   */
  public function access(Group $group_learning_path, Group $group_organization) {
    $user = \Drupal::currentUser();
    if (wind_does_user_has_sudo($user)){
      return AccessResult::allowed();
    }
    if($group_organization->getMember($user)){
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }
}
