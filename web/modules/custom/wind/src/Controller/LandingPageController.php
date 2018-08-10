<?php

namespace Drupal\wind\Controller;

class LandingPageController {
  public function content(){
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello, World!'),
    );
  }
}