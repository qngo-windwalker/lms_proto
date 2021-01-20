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
use Symfony\Component\HttpFoundation\Response;

class QDevelDevPageController{

  /**
   * Out for page [site]/qdev
   * @return array
   */
  public function getContent(){
//    $page = new \Drupal\q_devel\Controller\QDevelUserController();
//    $value = $page->createIndividualAccount();

    $array1 = array("a" => "green", "red", "blue", "red");
    $array2 = array("a" => "violet", "yellow", "red", 'orange');
    $result = _wind_array_compare($array1, $array2);

    $output = print_r($result, true);
//    return array(
//      '#type' => 'markup',
//      '#markup' => 'bbtesting'
//    );
    return new Response('<pre>' . $output . '</pre>', 200, array());
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
