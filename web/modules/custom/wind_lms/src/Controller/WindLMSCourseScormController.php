<?php

namespace Drupal\wind_lms\Controller;

class WindLMSCourseScormController{

  public function scormCommit($opigno_scorm_id, $opigno_scorm_sco_id) {
    if (!empty($_POST['data'])) {
      $scorm_service = \Drupal::service('opigno_scorm.scorm');
      $scorm = $scorm_service->scormLoadById($opigno_scorm_id);
      \Drupal::moduleHandler()->invokeAll('opigno_scorm_commit', [
        $scorm,
        $opigno_scorm_sco_id,
        json_decode($_POST['data']),
      ]);
      return new JsonResponse(['success' => 1]);
    } else {
      return new JsonResponse(['error' => 1, 'message' => 'no data received']);
    }
  }

}
