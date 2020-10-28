<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;

class WindTincanAdminUserCourseProgressesDatatableController extends ControllerBase{
  public function getContent() {
    $collection = [];
    $result = \Drupal::entityQuery('user')
      ->execute();

    foreach ($result as $uid) {
      if($uid == 0 ){
        continue;
      }
      $user = User::load($uid);
//      $licenseNode = $this->getUserLicense($uid);
      $coursesData = _wind_tincan_get_user_all_assigned_course_data($user);
      $collection[] = [
        'uid' => $uid,
        'username' => $this->getUserNameLink($user),
        'status' => $user->get('status')->value == 0 ? 'Inactive' : 'Active',
        'mail' => $user->get('mail')->value,
        'fullName' => $user->get('field_first_name')->value . ' ' . $user->get('field_last_name')->value,
        'created' => $user->get('created')->value,
        'login' => $user->get('login')->value,
//        'field_service_desk_account_id' => $user->get('field_service_desk_account_id')->value,
//        'jiraServiceDeskCustomerLink' => $this->getJiraServiceDeskCustomerLink($user->get('field_service_desk_account_id')->value),
//        'licenseLink' => $licenseNode ? $this->getLicenseLink($licenseNode) : '',
//        'field_paid' => $licenseNode ? $licenseNode->get('field_paid')->value : '',
//        'field_subscription_type' => $licenseNode ? $licenseNode->get('field_subscription_type')->value : '',
//        'field_clearinghouse_role' => $licenseNode ? $licenseNode->get('field_clearinghouse_role')->value : '',
//        'field_payment_date' => $licenseNode ? $licenseNode->get('field_payment_date')->value : '',
//        'field_payment_amount' => $licenseNode ? $licenseNode->get('field_payment_amount')->value : '',
//        'field_enroll_date' => $licenseNode ? $licenseNode->get('field_enroll_date')->value : '',
        'courseTitle' => $this->getCourseDataValue($coursesData, 'title'),
        'courseTincanId' => $this->getCourseDataValue($coursesData, 'tincan_course_id'),
        'courseProgress' => $this->getCourseDataValue($coursesData, 'progress'),
        'stored_date' => $this->getCourseDataValue($coursesData, 'stored_date'),
      ];
    }
    return new JsonResponse(['data' => $collection]);
  }

  function getCourseDataValue($coursesData, $key){
    $end = end($coursesData);
    switch ($key) {
      case 'stored_date' :
        $statement = $end['statement'];
        if (!$statement) {
          return '';
        }
        $stored_date = $statement->get('stored_date')->value;
        return $this->formatTime($stored_date);
        break;
      default:
        return isset($end[$key]) ? $end[$key] : '';
    }
    return '';
  }

  private function formatTime($timestamp) {
    if ($timestamp) {
      return date('m-d-Y', $timestamp);
    } else {
      // If the $timestamp is 0
      return 'Never';
    }
  }

  /**
   * @param \Drupal\node\Entity\Node $licenseNode
   *
   * @return \Drupal\Core\GeneratedLink
   */
  private function getLicenseLink($licenseNode) {
    $URL = Url::fromUserInput(
      "/node/{$licenseNode->id()}",
      array(
        'attributes' => array('class' => 'card-link')
      )
    );
    $linkContent = '<i class="fas fa-pen"></i> ' . $licenseNode->label();
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $URL)->toString();
  }

  /**
   * @param $uid
   *
   * @return boolean|\Drupal\Core\Entity\EntityInterface|null
   */
  private function getUserLicense($uid) {
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'license')
      ->condition('field_enrolled_user', $uid)
      ->execute();

    if (empty($result)) {
      return false;
    }
    $nid = _wind_array_first_child_value($result);
    return Node::load($nid);
  }

  private function getJiraServiceDeskCustomerLink($jiraCustomerId) {
    if ($jiraCustomerId) {
      // Todo Figure out how to generate JQL string.
      //      $query = 'reporter = ' . $jiraCustomerId . ' AND project = 10000 AND resolution is not EMPTY AND "Request Type" is not EMPTY';
      $jiraCustomerIdEncoded = str_replace(':', '%3A', $jiraCustomerId);
      $params = 'reporter%20%3D%20' . $jiraCustomerIdEncoded . '%20AND%20project%20%3D%2010000%20AND%20resolution%20is%20not%20EMPTY%20AND%20%22Request%20Type%22%20is%20not%20EMPTY';
      $urlString = 'https://clearinghousenavigator.atlassian.net/browse/ESD-68?jql=' . $params;
      return '<a target="_blank" href="'. $urlString . '">' . $jiraCustomerId . '</a>';
    }
    return '';
  }

  private function getUserNameLink(\Drupal\Core\Entity\EntityInterface $user) {
    $URL = Url::fromUserInput("/user/{$user->id()}");
    $linkContent = $user->label();
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $URL)->toString();
  }

}
