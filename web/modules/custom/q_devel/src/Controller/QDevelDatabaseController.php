<?php

namespace Drupal\q_devel\Controller;

//use Drupal\block\Entity\Block;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Enity\Node;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;
//
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\group\Entity\Group;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;
use Drupal\jira_rest\JiraRestWrapperService;

class QDevelDatabaseController{

  public function getContent(){
    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
  }


  /**
   * @param $group
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @see \Drupal\opigno_learning_path\Controller\LearningPathManagerController::addItem
   */
  private function addModuleToGroup($group) {
    $entity = OpignoModule::create(array(
      'name' => 'Fan Screening Course',
      'status' => true,
    ));
    $entity->save();

    // Create the added item as an LP content.
    $new_content = OpignoGroupManagedContent::createWithValues(
      $group->id(),
      'ContentTypeModule',
      $entity->id()
    );
    $new_content->save();

    //        $new_link = OpignoGroupManagedLink::createWithValues(
    //          $group->id(),
    //          $parentCid,
    //          $new_content->id()
    //        );
    //        $new_link->save();

    $added_entity = \Drupal::entityTypeManager()
      ->getStorage('opigno_module')
      ->load($entity->id());
    $group->addContent($added_entity, 'opigno_module_group');

    //
    //        /* @var $connection \Drupal\Core\Database\Connection */
    //        $connection = \Drupal::service('database');
    //        $insert_query = $connection->insert('opigno_module_result_options')
    //          ->fields([
    //            'module_id',
    //            'module_vid',
    //            'option_name',
    //            'option_summary',
    //            'option_summary_format',
    //            'option_start',
    //            'option_end',
    //          ]);
    //
    //        $insert_query->execute();

    //        $related = $this->getRelatedGroupContent($id);

  }

  public function loadGroupMultiple() {
    $query = \Drupal::entityQuery('group');
    $result = $query->execute();

    if ($result) {
      return $result;
    } else {
      return array();
    }
  }

  public function dbQuery($uid, $scorm_id) {
    $data = NULL;
    $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
      ->fields('o', array('value', 'serialized'))
      ->condition('o.uid', $uid)
      ->condition('o.scorm_id', $scorm_id)
//      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    }

    return $data;
  }

  public function entityQuery() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
//    $query->condition('parent_content_id', $this->id());
//    $query->condition('required_score', $user_score, '<=');
//    $query->sort('required_score', 'DESC');
//    $query->range(0, 1);
    $result = $query->execute();

    // If no result, return FALSE.
    if (empty($result)) {
      return FALSE;
    }
  }


  public function databaseQuery(){
    $result = db_select('opigno_scorm_packages', 'o')
      ->fields('o', array('value', 'serialized'))
      //      ->condition('o.scorm_id', $scorm_id)
      //      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    }

    return $data;
  }

  /**
   * Callback handler for wind-dev/user/{user}.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   */
  public function userContent(AccountInterface $user) {
    // Do something with $user.

  }

  public function createGroup($title) {
    $group = \Drupal\group\Entity\Group::create([
        'type' => 'learning_path',
        'label' => $title,
      ]
    );
    $group->enforceIsNew();
    $group->save();

    return $group;
  }

  public function setFileRecord($filepath){
    $parsed_url = UrlHelper::parse($filepath);
    $filepath = $parsed_url['path'];
    $contents = file_get_contents($filepath);
    $file_name = drupal_basename($filepath);
    // Prepare folder.
    $temporary_file_path = 'public://external_packages/' . $file_name;
    /** @var \Drupal\file\FileInterface|false $file */
    $result = file_save_data($contents, $temporary_file_path);
    $file = \Drupal\file\Entity\File::load($result->id());
    return $file;
  }


  public function createUser() {
    $user = User::create([
      'name' => 'manager3',
      'mail' => 'quan.ngo@windwalker.com',
      'status' => 1,
      'roles' => array('user_manager'),
    ]);
    $user->save();
    return $user;
  }

  public function blockDev() {
//   Block::load('wind_theme_dashboard_views_block_who_s_online-who_s_online_block')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_opigno_notifications-block_unread_dashboard')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_private_message-block_dashboard')->disable()->save();
//   Block::load('wind_theme_breadcrumbs')->disable()->save();
    $block = Block::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
    //    $user_login_block = \Drupal::entityTypeManager()->getStrorage('block_content')->load('seven_login');
    //    dsm($user_login_block);
    //    $block = \Drupal\block_content\Entity\BlockContent::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);

  }

  public function createNode() {
    $node = Node::create(array(
      'title' => 'New Node',
      'body' => 'Node body content',
      'type' => 'article',
      'field_image' => ['target_id' => $file->id(), 'title' => 'This is a file title']
    ));
    $node->save();
  }

  private function getRelatedGroupContent($gid) {
    $result = \Drupal::entityQuery('group_content')
      ->condition('gid', $gid)
      ->execute();

    if ($result) {
      $relations = \Drupal\group\Entity\GroupContent::loadMultiple($result);
      foreach ($relations as $relation) {
        $entity = $relation->getEntity();
        $group = $relation->getGroup();
        $a = $entity;
        if ($entity->getEntityTypeId() == 'opigno_module') {

        }
      }
    }

  }

  private function databaseInsert() {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $insert_query = $connection->insert('opigno_module_result_options')
      ->fields([
        'module_id',
        'module_vid',
        'option_name',
        'option_summary',
        'option_summary_format',
        'option_start',
        'option_end',
      ]);

    $insert_query->values(array(
      'module_id' => $this->id(),
      'module_vid' => $this->getRevisionId(),
      'option_name' => $option['option_name'],
      'option_summary' => $option['option_summary'],
      'option_summary_format' => $option['option_summary_format'],
      'option_start' => $option['option_start'],
      'option_end' => $option['option_end'],
    ));

    $insert_query->execute();
  }

}
