<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Link;
use Drupal\Core\Url;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoActivity;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\user\UserInterface;

class WindLMSCourseUsersController extends ControllerBase{

  public function getContent($group) {
    $table = $this->getDataTableRenderable('user-tbl', '/wl-datatable/org/' . $group->id(). '/users');
    $markup = '<div class="col-12-md">';
    $markup .= render($table);
    $markup .= '</div>';
    return [
      '#markup' => $markup,
    ];
  }

  public function getTitle($group) {
    return $group->label();
  }

  private function getDataTableRenderable($datatableURL) {
    $header = [
      array('data' => 'Active', 'class' => 'node-first-name-header'),
      array('data' => 'First Name', 'class' => 'node-first-name-header'),
      array('data' => 'Last Name', 'class' => 'node-last-name-header'),
      array('data' => 'Email', 'class' => 'node-email-header'),
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
        'id' => 'users-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          'wind_lms/datatable_users'
        ),
        'drupalSettings' => array(
          'wind_lms' => array(
            'datatableURL' => $datatableURL,
            'datatableElementId' => '#users-tbl'
          )
        )
      )
    ];
  }

}
