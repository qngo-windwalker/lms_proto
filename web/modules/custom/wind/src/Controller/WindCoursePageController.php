<?php

namespace Drupal\wind\Controller;

class WindCoursePageController{

  public function content(){

    return array(
      '#type' => 'markup',
      '#markup' => 'This is the course page.'
    );

  }

}