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
    $form_class = '\Drupal\wind_scorm\Form\WindScormFileFormAdd';

    $build =  [
      'content' => [
        '#markup' => ''
      ],
    ];
    $build['form'] = \Drupal::formBuilder()->getForm($form_class);
    return $build;
  }
}
