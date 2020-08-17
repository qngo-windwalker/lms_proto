<?php

namespace Drupal\wind\Controller;

class WindEspanolController {
  public function getContent(){
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello, World!'),
    );
  }
}
