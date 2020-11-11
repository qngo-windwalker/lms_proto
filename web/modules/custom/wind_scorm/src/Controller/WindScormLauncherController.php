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
    return '<p>Click ' . $link . ' to relaunch the course.';
  }

  private function getReturnLink() {
    $url = Url::fromUserInput(
      '/dashboard',
      [
        'attributes' => [
          'class' => ''
        ]
      ]
    );
    $linkContent = '<i class="fas fa-arrow-circle-left"></i> Return to Dashboard';
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }
}
