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

    if(!empty($fids)){
      $files = \Drupal::entityTypeManager()->getStorage('file')->loadMultiple($fids);

      foreach ($files as $file){
        $scorm = $scorm_controller->scormLoadByFileEntity($file);
        // If this scorm already extracted, skip it.
        if ($scorm) {
          continue;
        }

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
    $user = \Drupal\user\Entity\User::load($uid);
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'wind_lms';
    $key = 'create_article';
    $to = $user->get('mail')->value;
//    $params['message'] = $entity->get('body')->value;
    $params['message'] = 'You have been enrolled to course: ' . $node->label();
    $params['node_title'] = 'node title';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent.');
    } else {
      \Drupal::messenger()->addMessage('Your message has been sent.');
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
