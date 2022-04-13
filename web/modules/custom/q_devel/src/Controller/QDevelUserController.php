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

class QDevelUserController{

  public function getContent(){
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

  public function createIndividualAccount() {
    $pass = 'test';
    $users = array(
      array(
        'name' => 'test_ws1bca',
        'pass' => $pass,
        'mail' => 'shawnwhiddon@gmail.com',
        'first_name' => 'Shawn',
        'last_name' => 'Whiddon',
        'title' => 'President',
        'clearinghouse_role' => 'Employer',
        'company' => 'Truckway',
        'street_address' => '1429 New Creek Road',
        'city' => 'Fort Wayne',
        'us_state' => 'IN',
        'postal_zip_code' => '26802',
        'phone' => '2602182793',
        'country' => 'US',
//        'group_account' => true,
        'group_account' => false,
        'number_of_users' => 1,
      )
    );

    $com = new \Drupal\ch_nav\Commands\ChNavCommands();
//
    foreach ($users as $userInfo) {
//      $com->createUser($userInfo);
    }
  }

  public function createUserForCompany() {
    $pass = 'test';
    $users = array(
      array(
        'name' => 'test_ws1bca',
        'pass' => $pass,
        'mail' => 'shawnwhiddon@gmail.com',
        'first_name' => 'Shawn',
        'last_name' => 'Whiddon',
        'title' => 'President',
        'clearinghouse_role' => 'Employer',
        'company' => 'Truckway',
        'street_address' => '1429 New Creek Road',
        'city' => 'Fort Wayne',
        'us_state' => 'IN',
        'postal_zip_code' => '26802',
        'phone' => '2602182793',
        'country' => 'US',
        'group_account' => true,
        'group_account' => false,
        'number_of_users' => 5,
      )
    );
    $com = new \Drupal\ch_nav\Commands\ChNavCommands();
//
    foreach ($users as $userInfo) {
//      $com->createUser($userInfo);
    }
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


  public function entityQuery() {
    $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
//    $query->range(0, 1);
    $result = $query->execute();

    // If no result, return FALSE.
    if (empty($result)) {
      return FALSE;
    }
  }


  /**
   * Callback handler for wind-dev/user/{user}.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   */
  public function userContent(AccountInterface $user) {
    // Do something with $user.
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

  public function createNode() {
    $node = Node::create(array(
      'title' => 'New Node',
      'body' => 'Node body content',
      'type' => 'article',
    ));
    $node->save();
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
