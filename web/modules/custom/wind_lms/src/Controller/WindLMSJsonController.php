<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\wind_tincan\Entity\TincanStatement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\Response;

class WindLMSJsonController extends ControllerBase {

  public function getCurrentUser(){
    $user = $this->currentUser();

    return new JsonResponse([
      'uid' => $user->id(),
      'name' => $user->getAccountName()
    ]);
  }

  /**
   * Render Json for userCourseTable.js ajax
   * Path: [domain]/wind-lms/json/dashboard
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getCurrentDashboard(){
    $user = $this->currentUser();
    $userAccount = User::load($user->id());
    $data = $this->getUserData($userAccount);

    // For debugging
    if(\Drupal::request()->get('pretty') == 'true') {
      // This can use up lots of memory even with 4GB of memory_limit
      $output = '<pre>' . print_r($data, TRUE) . '</pre>';
      return new Response($output, 200, array());
    }

    return new JsonResponse($data);
  }

  /**
   * Render Json for Dashboard side-modal
   * path: 'wl-json/user/{user}'
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getUser(User $user) {
    $data = $this->getUserData($user);
    return new JsonResponse($data);
  }

  public function getUserVRCourse(User $user){
    // Current user is what's in the sesssion, argument $user is what we are inquiring.
    if(!$this->currentUser()->isAuthenticated()){
      return new JsonResponse([
        'message' => 'Not logged in.',
      ]);
    }
    // Get node vr_learning_object that has entity referece to $user.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'vr_learning_object');
    $query->condition('field_user', $user->id());
    $result = $query->execute();
    $vr = null;
    if($result){
      // We only expect 1 VR course linked to each user right now.
      $nid = array_shift($result);
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $vr = array(
        'title' => $node->label(),
        'nid' => $nid,
        'uuid' => $node->uuid(),
        'type' => $node->getType(),
        'field_completion_percentage' => $node->get('field_completion_percentage')->getString()
      );
    }

    return new JsonResponse([
      'uid' => $user->id(),
      'name' => $user->getAccountName(),
      'vr_learning_object' => $vr,
    ]);
  }

  /**
   * wind_lms.json.all_users_progress:
   *   path: 'wl-json/all-users-progress'
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getAllUsersProgress() {
    $collection = [];
    $result = \Drupal::entityQuery('user')
      ->execute();

    foreach ($result as $uid) {
      if($uid == 0 ){
        continue;
      }
      $user = User::load($uid);
      //      $licenseNode = $this->getUserLicense($uid);
      //      $coursesData = _wind_tincan_get_user_all_assigned_course_data($user);
      $coursesData = _wind_lms_get_user_all_assigned_course_data($user , \Drupal::request()->get('lang'));
      foreach ($coursesData as $course){
        $collection[] = [
          'user' => \Drupal\wind_lms\WindLMSJSONStructure::getUser($user),
          'course_nid' => $course['nid'],
          'uid' => $uid,
          'username' => $user->label(),
          'user_link' => $this->getUserNameLink($user),
          'status' => $user->get('status')->value,
          'mail' => $user->get('mail')->value,
          'fullName' => $user->get('field_first_name')->value . ' ' . $user->get('field_last_name')->value,
          'created' => $user->get('created')->value,
          'login' => $user->get('login')->value,
          'field_clearinghouse_role' =>  '',
          'field_enroll_date' => '',
          'courseTitle' => $this->getCourseDataValue($course, 'title'),
          'courseTincanId' => $this->getCourseDataValue($course, 'tincan_course_id'),
          'courseProgress' => $this->getCourseDataValue($course, 'progress'),
          'stored_date' => '',
          'package_files' => $course['package_files'],
          'certificateLink' => $this->getCourseCertificate($course, $user),
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  private function getUserData(User $user) {
    $rows = array();
    $coursesData = _wind_lms_get_user_all_assigned_course_data($user , \Drupal::request()->get('lang'));
    foreach ($coursesData as $courseData) {
      $rows[] = $this->buildCourseRow($courseData, $user);
    }
    return [
      'uid' => $user->id(),
      'username' => $user->getAccountName(),
      'name' => $user->getAccountName(),
      'full_name' => _wind_lms_get_user_full_name($user),
      'status' => $user->get('status')->value,
      'mail' => $user->get('mail')->value,
      'access' => $user->get('access')->value,
      'login' => $user->get('login')->value,
      'user_courses' => $rows
    ];
  }

  protected function buildCourseRow($courseData, $user) {
    $title = $courseData['title'];

    if($courseData['type'] == 'curriculum'){
      return [
        'data' => array(
          'type' => $courseData['type'],
          'title' => $courseData['title'],
//          'certificateLink' => $this->getCourseCertificate($courseData),
          'certificateLink' => '',
          'courses' => $this->buildCurriculumCourses($courseData['courses']),
          'nid' => isset($courseData['nid']) ? $courseData['nid'] : '',
        ),
        'class' => array('course-row'),
      ];
    }

    return [
      'data' => array(
        'title' => $courseData['title'],
        'type' => $courseData['type'],
        'courseLink' => $this->buildCourseLink($title, $courseData),
        'certificateLink' => $this->getCourseCertificate($courseData, $user),
        'package_files' => isset($courseData['package_files']) ? $courseData['package_files'] : [],
        'nid' => isset($courseData['nid']) ? $courseData['nid'] : '',
      ),
      'class' => array('course-row'),
      'data-tincan-id' => isset($courseData['tincan_course_id']) ?  $courseData['tincan_course_id'] : ''
    ];
  }

  protected function buildCurriculumCourses($couses) {
    $collection = array();
    foreach ($couses as $course) {
      $collection[] = $this->buildCourseRow($course);
    }
    return $collection;
  }

  protected function buildCourseLink($title, $courseData) {
    if(!isset($courseData['folder'])){
      return '';
    }
    $course_folder = $courseData['folder'];
    $linkContent = '<i class="fas fa-external-link-alt align-self-center pr-1"></i> ' . "<span> {$title}</span>";
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput(
      '/course/' . $course_folder,
      [
        'attributes' => [
          'data-coure-href' => _wind_lms_tincan_gen_static_course_link($course_folder),
          'class' => array('wind-scorm-popup-link', 'd-flex')
        ]
      ]
    );
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  /**
   * @param array $courseData
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\GeneratedLink|string
   */
  private function getCourseCertificate($courseData, $user) {
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
    return $allCompleted ? $this->buildCourseCertificateLink($courseData, $user) : 'N/A';
  }

  /**
   * Generate Certificate Id that can be decoded for traceability
   * @param array $courseData
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\GeneratedLink
   */
  private function buildCourseCertificateLink($courseData, User $user) {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('wind_lms')->getPath();
    $linkContent = '<img width="26" src="/' . $module_path . '/img/certificate_icon.png">';
    $renderedAnchorContent = render($linkContent);

    // Separate each structure with 00.
    $transaction_id = _wind_lms_encode_certificate_id($courseData);

    $url = Url::fromUserInput(
      '/certificate/' . $transaction_id . '/' . $user->id(),
      [
        'attributes' => ['target' => '_blank'],
      ]
    );

    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  function getCourseDataValue($course, $key){
    switch ($key) {
      case 'stored_date' :
        $statement = $course['statement'];
        if (!$statement) {
          return '';
        }
        $stored_date = $statement->get('stored_date')->value;
        return $this->formatTime($stored_date);
        break;
      default:
        return isset($course[$key]) ? $course[$key] : '';
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

  private function getUserNameLink(\Drupal\Core\Entity\EntityInterface $user) {
    $URL = Url::fromUserInput("/user/{$user->id()}");
    $linkContent = $user->label();
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $URL)->toString();
  }
}
