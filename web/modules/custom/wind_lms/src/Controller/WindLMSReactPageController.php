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
use Symfony\Component\HttpFoundation\Response;

class WindLMSReactPageController extends ControllerBase{

  public function getCourseContent(){
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

  public function getTeamContent() {
    return [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'react-container',
      ],
      '#attached' => [
        'library' => [
          'wind_lms/team',
        ],
      ]
    ];
    return $response;
  }

  public function getUserContent() {
    $html = $this->getPageTemplate('<script src="/modules/custom/wind_lms/js/dist/wind_lms.team.bundle.js"></script>');
    return new Response($html, Response::HTTP_OK,  array('content-type' => 'text/html'));
  }

  public function getNodeBulkEditContent(Node $node) {
    return [
      'content' => [
        '#markup' => $node->get('body')->value
      ],
      'form' => \Drupal::formBuilder()->getForm(\Drupal\wind_lms\Form\WindLMSCourseBulkEditForm::class, $node)
    ];
    return $response;
  }

  public function getTitle(Node $node) {
    return  $node->label();
  }

  private function getPageTemplate($jsFile){
    if(\Drupal::request()->get('browserSync') == 'true'){
      $host = \Drupal::request()->get('host');
      $jsFile .= "
      <script id=\"__bs_script__\">//<![CDATA[
              document.write(\"<script async src='http://HOST:{$host}/browser-sync/browser-sync-client.js?v=2.27.5'><\/script>\".replace(\"HOST\", location.hostname));
          //]]></script>
      ";
    }
    return '<!doctype html>
      <html lang="en">
        <head>
          <meta charset="utf-8" >
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" >
          <title>WindwalkerLearning</title>
        </head>
        <body>
          <div id="react-container"></div>
          ' . $jsFile . '
        </body>
      </html>';
  }
}
