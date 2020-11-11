<?php

namespace Drupal\wind_scorm\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindScormPackageAddController extends ControllerBase{

  public function getContent() {
//    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
//    $scorm_controller->scormExtract($file);

    $form_class = '\Drupal\wind_scorm\Form\WindScormFileFormAdd';

    $output = '<p>We launched your course in a new window but if you do not see it, a popup blocker may be preventing it from opening. Please disable popup blockers for this site.</p>';
    $build =  [
      'content' => [
        '#markup' => $output
      ],
    ];
    $build['form'] = \Drupal::formBuilder()->getForm($form_class);
    return $build;
  }

}
