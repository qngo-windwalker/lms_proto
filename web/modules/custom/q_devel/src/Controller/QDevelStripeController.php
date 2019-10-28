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

class QDevelStripeController{

  public function getContent(){
//    $customer = \Stripe\Customer::create([
//      'email' => $account->getEmail(),
//      'name' => 'test quan.ngo.com',
//      'description' => 'test account',
//      'phone' => '9196017030',
//      'metadata' => array(
//        'drupal_uid' => $account->id(),
//        'drupal_host' => $schemaAndHost,
//      ),
//    ]);
    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
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
