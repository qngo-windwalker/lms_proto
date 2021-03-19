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
   * OpignoScorm constructor.
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

    // Notify selected users
    if($field_learner_access == '0'){
      $field_learner_ids = $this->array_get_target_id($node->get('field_learner')->getValue());
      foreach ($field_learner_ids as $uid){
        $this->sendEmail($node, $uid);
      }
    }

    // Notify User Team.
    // Get all of the Team (taxonomy) in the course
    $tids = array_map (function($item){
      return $item['target_id'];
    }, $node->get('field_user_team')->getValue());
    // Find all users belong in all the teams
    $uids = _wind_lms_get_all_users_in_teams_by_tids($tids);
    foreach ($uids as $uid){
      $this->sendEmail($node, $uid);
    }
  }

  /**
   * Invoked by wind_lms_node_update()
   * @param \Drupal\node\NodeInterface $node
   */
  public function onNodeUpdate(NodeInterface $node){
    // Check and process any changes to Package File upload field
    $this->onNodeUpdateProcessField_package_file($node);
    // Check and process any changes to the combination of Accessible To All Leaners and Learner fields
    $this->onNodeUpdateProcessField_learner($node);
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
  private function onNodeUpdateProcessField_learner(NodeInterface $node) {
    /** @var  NodeInterface $originalNode */
    $originalNode = $node->original;
    $originalNode_field_learner_access = $originalNode->get('field_learner_access')->getString();

    $field_learner_access = $node->get('field_learner_access')->getString();
    // If user turns on "Accessible To All Leaners"
    if($originalNode_field_learner_access == '0' && $field_learner_access == '1'){
      $originalNode_field_learner_ids = $this->array_get_target_id($originalNode->get('field_learner')->getValue());
      // Send email to all user, but exclude users from original $node field learner so they won't get duplicate email.
      $this->notifyAllUser($node, $originalNode_field_learner_ids);
    } else {

      // If "Accessible To All Leaners" is turn off,
      // process the custom list of users
      if($field_learner_access == '0'){
        // Get all of the IDs nested in the array
        $originalNode_field_learner_ids = $this->array_get_target_id($originalNode->get('field_learner')->getValue());
        $field_learner = $node->get('field_learner')->getValue();
        $field_learner_ids = $this->array_get_target_id($field_learner);

        $result = _wind_array_compare($field_learner_ids, $originalNode_field_learner_ids);
        if (empty($result['added'])) {
          return;
        }
        // Send email to each user.
        foreach ($result['added'] as $uid){
          $this->sendEmail($node, $uid);
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
    foreach ($uids as $uid){
      if(in_array($uid, $uid_to_skip)){
        continue;
      }
      $this->sendEmail($node, $uid);
    }
  }

  /**
   * Compose email and sent it
   * @param \Drupal\node\NodeInterface $node
   * @param $uid
   */
  private function sendEmail(NodeInterface $node, $uid) {
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal\user\Entity\User::load($uid);
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . _wind_get_greeting_time() . ' ' . $user_full_name . ', </b><br /></p>';
    $courseLink = '<p>' . _wind_gen_button_for_email($node->label(),  $_SERVER['HTTP_ORIGIN']  . '?destination=/dashboard') . '</p>';
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $debugInfo = '<p><!-- Course Id: ' . $node->id() . '- User Id: ' . $uid . ' --></p>';
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $user->get('mail')->value;
    $params['to'] = $to;
    $params['subject'] = 'New enrollment';
    $params['from_name'] = $site_mail;
    $params['to_name'] = $site_name;
    $params['reply_to'] = $site_mail;
    $params['message'] = 'New enrollment: ' . $node->label();
    $params['node_title'] = $node->label() ;
    $params['body'] = $greeting . 'A new training course is available to you. Please click on the link below to login and take the course: <br /><br /> '  . $courseLink . '<br /><br />' . $closingStatment . $debugInfo;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', 'New Enrollment', $to, $langcode, $params, $site_mail);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent.');
    } else {
      \Drupal::messenger()->addMessage("An enrollment notification  email has been send to {$to}.");
    }
  }
  /**
   * IN : array(['target_id' => 1], ['target_id' => 2])
   * Out: array(1, 2)
   * @param $array
   *
   * @return array
   */
  private function array_get_target_id($array){
    return array_map(function($value){
      return $value['target_id'];
    }, $array);
  }
}
