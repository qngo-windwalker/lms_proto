<?php

namespace Drupal\wind\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;

class WindTechnicalSupportController extends ControllerBase{
  public function getContent() {
    $build = [
      'container' => array(
        '#type' => 'container',
        '#attributes' => [
          'class' => array('row')
        ],
      ),
    ];
    $build['container']['left_side'] = array(
      '#type' => 'container',
      '#attributes' => [
        'class' => array('col-md-6')
      ],
      'content' => $this->getLeftSideBuild(),
    );
    $build['container']['right_side'] = array(
      '#type' => 'container',
      '#attributes' => [
        'class' => array('col-md-6')
      ],
//      'content' => $this->getRightSideBuild(),
    );
    return $build;
  }

  private function getLeftSideBuild() {
    return [
      'header' => array(
        '#markup' => '<h4>Ask a Question</h4>'
      ),
      'form' => \Drupal::formBuilder()->getForm(\Drupal\wind\Form\WindTechnicalSupportForm::class)
    ];
  }

  private function getRightSideBuild() {
    $currentUser = \Drupal::currentUser();

    return [
      '#markup' => ''
    ];
  }
}
