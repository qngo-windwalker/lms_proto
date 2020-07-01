<?php

namespace Drupal\wind\Controller;

class WindReportPageController{

  public function content(){
    $header = [
      array('data' => 'First Name', 'class' => 'user-first-name-header'),
      array('data' => 'Last Name', 'class' => 'user-last-name-header'),
      array('data' => 'Email', 'class' => 'user-email-header'),
      array('data' => 'Progress', 'class' => 'username-header'),
    ];

    $computed_settings = [
      'wind1' => 'wind_user1'
    ];

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#attributes' => array(
          'id' => 'learner-status-tbl',
          'class' => array('table' ,'table-striped', 'table-bordered')
        ),
      '#attached' => array(
        'library' => array(
          'wind/page-report'
        ),
        'drupalSettings' => array(
          'wind' => array(
            'managePage' => $computed_settings
          )
        )
      )
    ];
    return array(
      '#type' => 'markup',
      '#markup' => '<div>' . $table . '</div>'
    );

  }
}
