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

    /* @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');

    /* @var \Drupal\wind_tincan\WindTincanService $tincan_service */
    $tincan_service = \Drupal::service('wind_tincan.tincan');

    if(!empty($fids)){
      $files = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($fids);

      foreach ($files as $file){
        $scorm = $scorm_controller->scormLoadByFileEntity($file);
        // If this scorm already extracted, skip it.
        if ($scorm) {
          continue;
        }

//        $tincan_service->saveTincanPackageInfo($file);

        // Create SCORM package from file.
        $scorm_controller->scormExtract($file);
      }
    }

    // array_diff will return $originalFids values that are not in $fids.
    $diff = array_diff($originalFids, $fids);

    if (!empty($diff)) {
      foreach ($diff as $diff_file){
        // Todo: Remove file to save disc space.
      }
    }
  }

  private function onNodeUpdateProcessField_learner(NodeInterface $node) {
    /** @var  NodeInterface $originalNode */
    $originalNode = $node->original;
    $originalNode_field_learner_access = $originalNode->get('field_learner_access')->getString();

    $field_learner_access = $node->get('field_learner_access')->getString();
    // If user turns on "Accessible To All Leaners"
    if($originalNode_field_learner_access == '0' && $field_learner_access == '1'){
      $userStorage = \Drupal::entityTypeManager()->getStorage('user');
      $query = $userStorage->getQuery();
      $uids = $query
        ->condition('status', '1')
        ->execute();
      foreach ($uids as $uid){
        $this->sendEmail($node, $uid);
      }
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

  private function sendEmail(NodeInterface $node, $uid) {
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal\user\Entity\User::load($uid);
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . _wind_get_greeting_time() . ' ' . $user_full_name . ', </b><br /></p>';
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $courseLink = _wind_gen_button_for_email($node->label(),  $_SERVER['HTTP_ORIGIN']  . '?destination=/dashboard');
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
    $params['body'] = $greeting . 'A new training course is available to you. Please click on the link below to login and take the course: <br /><br /> '  . $courseLink . '<br />' . $closingStatment . $debugInfo;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', 'New Enrollment', $to, $langcode, $params, $site_mail);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent.');
    } else {
      \Drupal::messenger()->addMessage("An email has been send to {$to}.");
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
