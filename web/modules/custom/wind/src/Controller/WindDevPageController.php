<?php

namespace Drupal\wind\Controller;

class WindDevPageController{

  public function content(){

    return array(
      '#type' => 'markup',
      '#markup' => 'testing'
    );
  }
}
