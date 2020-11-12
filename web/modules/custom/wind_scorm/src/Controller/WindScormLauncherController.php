<?php

namespace Drupal\wind_scorm\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindScormLauncherController extends ControllerBase{

  public function getContent($id) {
    $output = '<p>We launched your course in a new window but if you do not see it, a popup blocker may be preventing it from opening. Please disable popup blockers for this site.</p>';
    $output .= $this->getReturnLink();
    $output .= $this->relaunchLink();
    return [
      'content' => [
        '#markup' => $output
      ],
    ];
  }

  public function courseRender() {
    return [
      'content' => [
        '#markup' => '<p>coming soon</p>',
      ],
    ];
  }

  public function getTitle($id) {
    $element = wind_scorm_get_player_rendable_array_by_scorm_id($id);
    if(!$element){
      return 'Error: Unable to locate SCROM Package.';
    }

    return $element['#start_sco']->title;
  }

  private function relaunchLink(){
    $module_handler = \Drupal::service('module_handler');
    $course_folder = $module_path = $module_handler->getModule('wind_scorm')->getPath() . '/courses/test-1';
    $linkContent = '<i class="fas fa-external-link-alt align-self-center pr-1"></i> ' . "<span>Test Scorm 1</span>";
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput(
      '/course/' . $course_folder,
      [
        'attributes' => [
          'data-coure-href' => _wind_lms_gen_course_link($course_folder),
          'class' => array('wind-scorm-popup-link', 'd-flex')
        ]
      ]
    );
    $link =  Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
    return '<p class="mt-5">Click the link below to relaunch the course.</p>' . $link;
  }

  private function getReturnLink() {
    if(isset($_GET['dest'])){
      $linkContent = '<i class="fas fa-arrow-circle-left"></i> Return to Previous Page';
      $url = Url::fromUserInput(
        $_GET['dest'],
        [
          'attributes' => [
            'class' => ''
          ]
        ]
      );
    } else {
      $linkContent = '<i class="fas fa-arrow-circle-left"></i> Return to Dashboard';
      $url = Url::fromUserInput(
        '/dashboard',
        [
          'attributes' => [
            'class' => ''
          ]
        ]
      );
    }
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }
}
