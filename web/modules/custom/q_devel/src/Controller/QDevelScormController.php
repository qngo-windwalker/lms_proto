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

class QDevelScormController{

  public function getContent(){
    //    wind_lms_add_user_to_group(1, 6);

//    $page = new \Drupal\wind_jira\Controller\WindJiraDevController;
//    $value = $page->createRequest();
//    $value = $page->addOrganization();
//    $value = $page->addOrganization();
//    $value = $page->addCustomerToOrganization();
//    $value = $page->createCustomer();
//    kint($value);

//    $currentUser = \Drupal::currentUser();
//    $org = wind_help_get_user_group_organization($currentUser);
//    $currentPath = \Drupal::service('path.current')->getPath();
//    $currentURI = \Drupal::request()->getUri();
//    $currentURI = \Drupal::request()->getRequestUri();
//    $serviceDeskId = (int) $org->get('field_service_desk_org_id')->getString();
//
//    if ($org->hasField('field_service_desk_org_id')) {
//      $serviceDeskId = (int) $org->get('field_service_desk_org_id')->getString();
//    }
//    wind_lms_add_user_to_group(1, 6);


    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
  }

  public function createUserB() {
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
}
