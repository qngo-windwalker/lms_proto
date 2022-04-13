<?php

namespace Drupal\q_devel\Controller;

//use Drupal\block\Entity\Block;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Controller\ControllerBase;
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
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

class QDevelDevPageController extends ControllerBase{

  /**
   * Out for page [site]/qdev
   * @return array
   */
  public function getContent(){

    $schema = Database::getConnection()->schema();
//    echo $schema->tableExists('wind_tincan_type_properties');
////    $this->createDBTable();
//    $array1 = array("a" => "green", "red", "blue", "red");
//    $array2 = array("a" => "violet", "yellow", "red", 'orange');
//    $result = _wind_array_compare($array1, $array2);

    $node = \Drupal\node\Entity\Node::load(107);
    $tids = array_map (function($item){
      return $item['target_id'];
    }, $node->get('field_user_team')->getValue());
    $uids = _wind_lms_get_all_users_in_teams_by_tids($tids);

//    $output = print_r($result, true);
//    return array(
//      '#type' => 'markup',
//      '#markup' => 'bbtesting'
//    );
    return new Response('<pre>' . $output . '</pre>', 200, array());
  }

  public function createDBTable() {
    $table = [
      'description' => 'Tincan activity properties.',
      'fields' => [
        'id' => [
          'type' => 'serial',
          'not null' => TRUE,
        ],
        'fid' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'activity_id' => [
          'description' => 'Activity id from Tincan Package.',
          'type' => 'text',
        ],
        'launch_filename' => [
          'type' => 'text',
        ],
      ],
      'indexes' => [
        'id' => ['id'],
      ],
    ];

    $t2 = [
      'description' => 'Tincan answer properties.',
      'fields' => [
        'uid' => [
          'description' => 'The user ID this data belongs to.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'opigno_activity_id' => [
          'description' => 'Opigno Activity id.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'registration' => [
          'description' => 'UUID each LRS connection.',
          'type' => 'text',
          'not null' => TRUE,
        ],
      ],
    ];
    $schema = Database::getConnection()->schema();
    $schema->createTable('wind_tincan_type_properties', $table);
    $schema->createTable('wind_tincan_answers', $t2);

  }
  public function testEmail() {
    //    $page = new \Drupal\q_devel\Controller\QDevelUserController();
    //    $value = $page->createIndividualAccount();
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');

    $renderable = [
      '#theme' => 'wind_email',
      '#body' => 'You have been enrolled to course: ' ,
    ];
    $rendered = \Drupal::service('renderer')->renderPlain($renderable);

    $params = [];
    /* @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
    $mailManager = \Drupal::service('plugin.manager.mail');
    //    $to = $user->get('mail')->value;
    $to = 'quan21@yahoo.com';
    $params['to'] = $to;
    $params['subject'] = 'New enrollment';
    $params['from_name'] = $site_mail;
    $params['to_name'] = $site_name;
    $params['reply_to'] = $site_mail;
    //    $params['body'] = check_markup('You have been enrolled to course: ' , 'plain_text');
    $params['body'] = $rendered;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', 'create_article', $to, $langcode, $params, $site_mail);

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

  /**
   * @param $title
   * @param $link
   * @return mixeduse
   * Drupal\Core\Link;
   * Drupal\Core\Url;
   */
  private function _l($title, $link) {
    $l = Link::fromTextAndUrl(
      $title,
      Url::fromUri("internal:/{$link}")
    );
    return $l->toString();
  }

  private function _l_fromRoute($title, $link) {
    $l = Link::fromTextAndUrl(
      $title,
      Url::fromRoute(
        'opigno_module.opigno_activities_browser',
        array('opigno_module' => $moduleId, 'opigno_activity' => $activity->id()),
        array('attributes' => array('target' => '_blank', 'class' => 'wind-scorm-popup-link'))
      )
    );
    return $l->toString();
  }

}
