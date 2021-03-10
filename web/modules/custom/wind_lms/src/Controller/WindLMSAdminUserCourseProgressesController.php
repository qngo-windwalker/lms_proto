<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;

class WindLMSAdminUserCourseProgressesController extends ControllerBase{

  public function getContent() {

//    \Drupal::configFactory()->get('system.performance')->set('js.preprocess', FALSE);
    $a = \Drupal::configFactory()->getEditable('system.performance')->set('js.preprocess', 1);

    $table = $this->getDataTableRenderable('user-course-progresses-tbl', "/wind-lms-course/course-progress-datatable/");
    $markup = '';
    $markup .= render($table);
    return [
      '#markup' => $markup,
    ];
  }

  private function getDataTableRenderable($tableElemntId, $datatableURL) {
    return [
      '#type' => 'table',
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => $tableElemntId,
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          'wind_lms/admin_user_course_progresses'
        ),
        'drupalSettings' => array(
          'windLMS' => array(
            'datatableURL' => $datatableURL,
            'datatableElementId' => '#' . $tableElemntId
          )
        )
      )
    ];
  }

}
