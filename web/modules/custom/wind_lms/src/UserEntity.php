<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class UserEntity.
 */
class UserEntity {

  protected $database;

  /**
   * UserEntity constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Invoked by wind_lms_user_insert()
   * @param \Drupal\Core\Entity\EntityInterface $user
   */
  public function onUserInsert(EntityInterface $user){
    $field_team_ids = $this->array_map_get_target_id($user->get('field_team')->getValue());

    if (empty($field_team_ids)) {
      return;
    }

    $this->sendEmailOfAllCourseAvailableToUser($field_team_ids, $user->id());
  }

  /**
   * Invoked by wind_lms_user_update()
   * @param \Drupal\Core\Entity\EntityInterface $user
   */
  public function onUserUpdate(EntityInterface $user){
    // Check if there's any change in field_team values
    $originalEnity = $user->original;
    $originalEntity_field_team_ids = $this->array_map_get_target_id($originalEnity->get('field_team')->getValue());
    $field_team_ids = $this->array_map_get_target_id($user->get('field_team')->getValue());
    $field_team_comparedResult = _wind_array_compare($field_team_ids, $originalEntity_field_team_ids);
    if (empty($field_team_comparedResult['added'])) {
      return;
    }

    $team_tids = $field_team_comparedResult['added'];
    $this->sendEmailOfAllCourseAvailableToUser($team_tids, $user->id());
  }

  /**
   * @param array $teamTids tag id of User Team vocabulary
   */
  private function sendEmailOfAllCourseAvailableToUser($teamTids, $uid) {
    // Get all courses that has our team assigned to it
    $courseNids = $this->getAllCoursesAssignedToTeams($teamTids);
    if (empty($courseNids)) {
      return;
    }

    $emailNotify = new CourseEmailNotification();
    $courseNodes = \Drupal\node\Entity\Node::loadMultiple($courseNids);
    foreach ($courseNodes as $node) {
      // Send out email now that this user is part of a new team.
      $emailNotify->sendEmail($node, $uid);
    }
  }

  /**
   * IN : array(['target_id' => 1], ['target_id' => 2])
   * Out: array(1, 2)
   * @param $array
   *
   * @return array
   */
  private function array_map_get_target_id($array){
    $newArray = [];
    // We use foreach loop instead of array_map so the key is the same as value
    // key same as value is useful when combining with array_merge to make sure NO duplicates.
    foreach ($array as $value) {
      $id = $value['target_id'];
      $newArray[$id] = $id;
    }
    return $newArray;
  }

  private function getAllCoursesAssignedToTeams(array $field_team_ids) {
    $userStorage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $userStorage->getQuery();
    $result = $query
      ->condition('type', 'course')
      ->condition('field_user_team', $field_team_ids, 'IN')
      ->condition('status', '1')
      ->execute();

    if ($result) {
      return $result;
    }

    return [];
  }

}
