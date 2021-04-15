<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\wind_tincan\Entity\TincanStatement;

class  WindTincanAdminTincanUserCourseController extends ControllerBase{

  /**
   * route: wind_tincan.admin.tincan:
   *  path: '/admin/tincan'
   * @param string $uid
   */
  public function getContent() {
    return [
      '#markup' => '<div><table id="tincan-tbl" ref="main" data-striping="1"></table></div>',
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_tincan'
        ),
        'drupalSettings' => array(
          'wind_tincan' => array(
            'datatableData' => $this->getAllUserData(),
          )
        )
      )
    ];
  }

  /**
   * route: wind_tincan.admin.tincan.user.course:
   *  path: '/admin/tincan/{uid}/course/{tincan_id}'
   * @param string $uid
   * @param string $tincan_id
   */
  public function getUserCourseContent($uid, $tincan_id) {
    $user = User::load($uid);
    $TincanIdDecoded = base64_decode($tincan_id);
    $info = "<h2>Username: {$user->label()} -- Uid: {$user->id()}</h2>";
    return [
      '#markup' => $info . '<div><table id="tincan-user-course-tbl" ref="main" data-striping="1"></table></div>',
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_tincan_user_course'
        ),
        'drupalSettings' => array(
          'wind_tincan' => array(
            'datatableData' => $this->getTincan($user, $TincanIdDecoded),
          )
        )
      )
    ];
  }

  public function getUserCourseTitle($uid, $tincan_id) {
    $user = User::load($uid);
    $TincanIdDecoded = base64_decode($tincan_id);
    $courses = _wind_tincan_get_user_all_assigned_course_data($user);
    foreach ($courses as $course) {
      if ($course['tincan_course_id'] == $TincanIdDecoded) {
        return $course['title'];
      }
    }
  }

  private function getAllUserData() {
    $data = [];
    $users = User::loadMultiple();
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue;
      }
      $data[] = [
        'user' => $this->getUserData($user),
        'courses' => $this->getUserCourses($user)
      ];
    }
    return $data;
  }

  private function getUserData(User $user) {
    return [
      'uid' => $user->id(),
      'username' => $user->label(),
      'agentId' => _wind_tincan_get_user_tincan_agent_id($user),
    ];
  }

  private function getTincan($user, $TC_COURSE_ID) {
    $agentID = _wind_tincan_get_user_tincan_agent_id($user);
    if (!$agentID) {
     return [];
    }

    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $TC_COURSE_ID);
    $query->condition('field_tincan_object.type', 'Activity');
    $query->condition('json', 'completion', 'CONTAINS');
    // Sort latest to oldest
    $query->sort('timestamp' , 'DESC');
    $result = $query->execute();
    if ($result) {
      $statementsDecoded = [];
      $statements = TincanStatement::loadMultiple($result);
      foreach ($statements as $statement ){
        $json_array = Json::decode($statement->get('json')->value);
        $statementsDecoded[] = $json_array;
      }
      return $statementsDecoded;
    }

    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $TC_COURSE_ID);
    $query->condition('json', 'experienced', 'CONTAINS');
    $result = $query->execute();
    return $result;
  }

  private function getUserCourses(User $user) {
    $courses = _wind_tincan_get_user_all_assigned_course_data($user);
    foreach ($courses as &$course) {
      $course['tincan_statements'] = $this->getTincan($user, $course['tincan_course_id']);
    }
    return $courses;
  }

}
