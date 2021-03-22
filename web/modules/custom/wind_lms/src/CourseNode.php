<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
/**
 * Class CourseNode.
 */
class CourseNode {

  protected $database;

  /**
   * CourseNode constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * @param array $courseData
   *
   * @return mixed|bool|string
   */
  static function isCourseCompleted($courseData) {
    // If there's no package_files, we treat this as a ILT course.
    if (!count($courseData['package_files'])) {
      // return Not Applicable will allow learner to upload their own certificate.
      return 'N/A';
    }

    $allCompleted = true;
    foreach ($courseData['package_files'] as $package_file) {
      // Scorm is completed, Tincan is Completed (uppercase)
      // strtolower to make it all lowercase.
      $progress = strtolower($package_file['course_data']['progress']);
      if($progress != 'completed'){
        $allCompleted = false;
        // will leave the foreach loop, if an item is not completed.
        break;
      }
    }
    return $allCompleted;
  }

  /**
   * Invoked by wind_lms_node_insert()
   * @param \Drupal\node\NodeInterface $node
   */
  public function onNodeInsert(NodeInterface $node){
    $field_package_file = $node->get('field_package_file')->getValue();
    if(empty($field_package_file)){
      return;
    }

    $fids = array_map(function($value){
      return $value['target_id'];
    }, $field_package_file);

    // Extract all of the files and process if it's SCORM or Tincan
    $this->extractPackageFiles($fids, $node);

    // Next, we handle user notification of new course
    $field_learner_access = $node->get('field_learner_access')->getString();
    // Notify all user
    if($field_learner_access == '1'){
      $this->notifyAllUser($node);
    }

    // Notify selected users and/or selected teams
    if($field_learner_access == '0'){
      $field_learner_ids = $this->array_map_get_target_id($node->get('field_learner')->getValue());

      // Get all of the Team (taxonomy) in the course
      $tids = array_map(function($item){
        return $item['target_id'];
      }, $node->get('field_user_team')->getValue());
      // Find all users belong in all the teams
      $user_team_uids = _wind_lms_get_all_users_in_teams_by_tids($tids);
      $user_team_uids = $this->array_key_same_as_value($user_team_uids);

      // Combine uids of field_team and field_learner
      $uids = array_merge($user_team_uids, $field_learner_ids);

      $emailNotify = new CourseEmailNotification();

      // Send email so user won't get duplicate
      foreach ($uids as $uid){
        $emailNotify->sendEmail($node, $uid);
      }
    }
  }

  /**
   * Invoked by wind_lms_node_update()
   * @param \Drupal\node\NodeInterface $node
   */
  public function onNodeUpdate(NodeInterface $node){
    // Check and process any changes to Package File upload field
    $this->onNodeUpdateProcessField_package_file($node);
    // Check and process any changes to the combination of Accessible To All Leaners, Learner fields, field_user_team
    $this->onNodeUpdateProcessUsers($node);
  }

  private function onNodeUpdateProcessField_package_file(NodeInterface $node) {
    /** @var  NodeInterface $originalNode */
    $originalNode = $node->original;
    $originalNodeField_package_file = $originalNode->get('field_package_file')->getValue();
    $originalFids = array_map(function($value){
      return $value['target_id'];
    }, $originalNodeField_package_file);

    $field_package_file = $node->get('field_package_file')->getValue();
    $fids = array_map(function($value){
      return $value['target_id'];
    }, $field_package_file);

    // Find out if there's any new file has been added on this edit session.
    $result = _wind_array_compare($fids, $originalFids);
    if (!empty($result['added'])) {
      // Extract all of the files and process if it's SCORM or Tincan
      $this->extractPackageFiles($result['added'], $node);
    }

    // array_diff will return $originalFids values that are not in $fids.
    $diff = array_diff($originalFids, $fids);

    if (!empty($diff)) {
      foreach ($diff as $diff_file){
        // Todo: Remove file to save disc space.
      }
    }
  }

  /**
   * @param $fids
   * @param $node
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function extractPackageFiles($fids, $node) {
    $files = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($fids);

    /* @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
    /* @var \Drupal\wind_tincan\WindTincanService $tincan_service */
    $tincan_service = \Drupal::service('wind_tincan.tincan');

    /** @var \Drupal\file\Entity\File $file */
    foreach ($files as $file){
      // Create SCORM package from file.
      $result = $scorm_controller->scormExtract($file);

      // If it's not SCORM zip, try Tincan
      if (!$result) {
        $tin_result = $tincan_service->saveTincanPackageInfo($file);

        if(!$tin_result){
          \Drupal::logger('wind_lms Course')->notice('Unable to process neither SCORM nor Tincan uploaded file for course %name id : %nid . File fid: %fid .',
            [
              '%nid' => $node->id(),
              '%name' => $node->label(),
              '%fid' => $file->id(),
            ]);
        }
      }
    }
  }

  /**
   * Node Update: process field_learner_access AND field_learner
   * @param \Drupal\node\NodeInterface $node
   */
  private function onNodeUpdateProcessUsers(NodeInterface $node) {
    /** @var  NodeInterface $originalNode */
    $originalNode = $node->original;
    $originalNode_field_learner_access = $originalNode->get('field_learner_access')->getString();

    $field_learner_access = $node->get('field_learner_access')->getString();
    // If user turns on "Accessible To All Leaners"
    if($originalNode_field_learner_access == '0' && $field_learner_access == '1'){
      $originalNode_field_learner_ids = $this->array_map_get_target_id($originalNode->get('field_learner')->getValue());
      // Send email to all user, but exclude users from original $node field learner so they won't get duplicate email.
      $this->notifyAllUser($node, $originalNode_field_learner_ids);
    }

    // If "Accessible To All Leaners" is turn off,
    // process the custom list of users
    if($field_learner_access == '0'){
      // Get all of the IDs nested in the array
      $originalNode_field_learner_ids = $this->array_map_get_target_id($originalNode->get('field_learner')->getValue());
      $field_learner_ids = $this->array_map_get_target_id($node->get('field_learner')->getValue());
      $result = _wind_array_compare($field_learner_ids, $originalNode_field_learner_ids);
      $user_uids = [];
      if (!empty($result['added'])) {
        $user_uids = $result['added'];
      }

      // Check if there's any change in field_user_team values
      $originalNode_field_user_team_ids = $this->array_map_get_target_id($originalNode->get('field_user_team')->getValue());
      $field_user_team_ids = $this->array_map_get_target_id($node->get('field_user_team')->getValue());
      $field_user_team_comparedResult = _wind_array_compare($field_user_team_ids, $originalNode_field_user_team_ids);
      if (!empty($field_user_team_comparedResult['added'])) {
        $team_uids = _wind_lms_get_all_users_in_teams_by_tids($field_user_team_comparedResult['added']);
        $user_uids = array_merge($user_uids, $team_uids);
      }

      if(!empty($user_uids)){
        $emailNotify = new CourseEmailNotification();
        // Send email to each user.
        foreach ($user_uids as $uid){
          $emailNotify->sendEmail($node, $uid);
        }
      }
    }
  }

  /**
   * Send email to user
   * @param $node
   * @param array $uid_to_skip
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function notifyAllUser($node, $uid_to_skip = []) {
    $userStorage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery();
    $uids = $query
      ->condition('status', '1')
      ->execute();
    $emailNotify = new CourseEmailNotification();
    foreach ($uids as $uid){
      if(in_array($uid, $uid_to_skip)){
        continue;
      }
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

  /**
   * Unsure the key value pair is the same.
   * Good for prepping before array_merge
   *
   * @param $arr
   */
  private function array_key_same_as_value($arr){
    $newArray = [];
    foreach ($arr as $value) {
      $newArray[$value] = $value;
    }
    return $newArray;
  }

  private function getUidsFromFromTeamTids(NodeInterface $node) {
    // Get all of the Team (taxonomy) in the course
    $tids = array_map(function($item){
      return $item['target_id'];
    }, $node->get('field_user_team')->getValue());
    // Find all users belong in all the teams
    $user_team_uids = _wind_lms_get_all_users_in_teams_by_tids($tids);
    $user_team_uids = $this->array_key_same_as_value($user_team_uids);
  }

}
