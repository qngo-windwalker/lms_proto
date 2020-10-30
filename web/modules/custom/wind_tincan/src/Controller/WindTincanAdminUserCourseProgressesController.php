<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Core\Controller\ControllerBase;

class WindTincanAdminUserCourseProgressesController extends ControllerBase{

  public function getContent() {

//    \Drupal::configFactory()->get('system.performance')->set('js.preprocess', FALSE);
    $a = \Drupal::configFactory()->getEditable('system.performance')->set('js.preprocess', 1);

    $table = $this->getDataTableRenderable('user-course-progresses-tbl', "/wind-tincan-course/course-progress-datatable/");
    $markup = '';
    $markup .= render($table);
    return [
      '#markup' => $markup,
    ];
  }

  private function getDataTableRenderable($tableElemntId, $datatableURL) {
    $header = [
      array('data' => 'Username', 'class' => 'header-username'),
      array('data' => 'Email', 'class' => 'header-email'),
      array('data' => 'Full Name', 'class' => 'header-first-name'),
      array('data' => 'License', 'class' => 'header-license'),
      array('data' => 'Paid', 'class' => 'header-license-paid'),
      array('data' => 'License Role', 'class' => 'header-license-role'),
      array('data' => 'Enroll Date', 'class' => 'header-license-role'),
      array('data' => 'Course', 'class' => 'header-license-role'),
      array('data' => 'Progress', 'class' => 'header-license-role'),
      array('data' => 'Activity Date', 'class' => 'header-license-role'),
    ];

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => $tableElemntId,
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_user_course_progresses'
        ),
        'drupalSettings' => array(
          'ch_nav' => array(
            'datatableURL' => $datatableURL,
            'datatableElementId' => '#' . $tableElemntId
          )
        )
      )
    ];
  }

}
