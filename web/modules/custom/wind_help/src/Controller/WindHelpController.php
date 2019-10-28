<?php

namespace Drupal\wind_help\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindHelpController extends ControllerBase{
  public function getContent() {
  $form = \Drupal::formBuilder()->getForm(\Drupal\wind_help\Form\WindHelpAtlassianRequestForm::class);
  return $form;
  }

  public function getTitle(Group $group_learning_path, Group $group_organization) {
    return $group_learning_path->label();
  }

  private function getLicensesSection($totalLicenses, $activeUserCount, $group_organization) {
    $url = Url::fromUserInput(
      "/org/{$group_organization->id()}/addlicenses",
      array(
        'query' => [
          'destination' => Url::fromRoute('<current>')->toString(),
        ],
        'attributes' => array('class' => '')
      )
    );
    $linkContent = '<i class="fas fa-plus-circle"></i> Add Licenses';
    $renderedAnchorContent = render($linkContent);
    $addLicensesLink = Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
    $percent = $activeUserCount / $totalLicenses;
    $output = '<div class="col-12-md mb-5">';
    $output .= '<h3>Licenses</h3>';
    $output .= '<div class="progress mb-4">';
    $output .= '<div class="progress-bar" data-width="'.$percent .'" aria-valuemax="100"></div>';
    $output .= '</div>';
    $output .= '  <div class="row">';
    $output .= '    <div class="col-md-4">Total Licenses: '. $totalLicenses . ' </div><div class="col-md-4">Used Licences: ' . $activeUserCount . ' </div><div class="col-md-4">' . $addLicensesLink . '</div>';
    $output .= '  </div>';
    $output .= '</div>';
    return $output;
  }

  private function getDataTableRenderable($tableElemntId, $datatableURL) {
    $header = [
      array('data' => 'First Name', 'class' => 'node-first-name-header'),
      array('data' => 'Last Name', 'class' => 'node-last-name-header'),
      array('data' => 'Email', 'class' => 'node-email-header'),
      array('data' => 'Enrollment', 'class' => 'node-enrollment-header'),
      array('data' => 'Last Login', 'class' => 'node-last-login-header'),
      array('data' => 'Last Accessed', 'class' => 'node-last-accessed-header'),
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
          'ch_nav/course_org'
        ),
        'drupalSettings' => array(
          'ch_nav' => array(
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
