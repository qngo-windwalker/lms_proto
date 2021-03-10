<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\node\Entity\Node;

class WindLMSCourseController extends ControllerBase{

  public function getContent(){
    $header = [
      array('data' => 'First Name', 'class' => 'node-first-name-header'),
      array('data' => 'Last Name', 'class' => 'node-last-name-header'),
      array('data' => 'Email', 'class' => 'node-email-header'),
      array('data' => 'Last Login', 'class' => 'node-last-login-header'),
      array('data' => 'Last Accessed', 'class' => 'node-last-accessed-header'),
      array('data' => 'Operations', 'class' => 'node-operations-header'),
    ];

    $tablConfig = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          'wind_lms/course'
        ),
        'drupalSettings' => array(
          'wind_lms' => array(
          )
        )
      )
    ];
    $markup = '<div class="col-12-md">';
    $markup .= '</div>';
    $markup .= '<div class="col-12-md">';
    $markup .= '<h3>Users</h3>';
    $markup .= render($tablConfig);
    $markup .= '<a class="btn btn-info" href="/course/1/adduser"><i class="fas fa-plus-circle"></i> Add User</a>';
    $markup .= '</div>';

    return [
      '#markup' => $markup,
    ];
  }

  public function getNodeContent(Node $node) {
    return [
      'content' => [
        '#markup' => $node->get('body')->value
      ]
    ];
    return $response;
  }

  public function getTitle(Node $node) {
    return  $node->label();
  }
}
