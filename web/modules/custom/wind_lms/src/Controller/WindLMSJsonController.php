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
    $rows = array();
    $coursesData = _wind_lms_get_user_all_assigned_course_data($user , \Drupal::request()->get('lang'));
    foreach ($coursesData as $courseData) {
      $rows[] = $this->buildCourseRow($courseData);
    }

    $data = [
      'uid' => $user->id(),
      'name' => $user->getAccountName(),
      'tableRow' => $rows
    ];

    // For debugging
    if(\Drupal::request()->get('pretty') == 'true') {
      // This can use up lots of memory even with 4GB of memory_limit
      $output = '<pre>' . print_r($data, TRUE) . '</pre>';
      return new Response($output, 200, array());
    }

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

  public function getAllUsersProgress() {
    return new JsonResponse([

    ]);
  }

  protected function buildCourseRow($courseData) {
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
        'certificateLink' => $this->getCourseCertificate($courseData),
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
          'data-coure-href' => _wind_lms_gen_course_link($course_folder),
          'class' => array('wind-scorm-popup-link', 'd-flex')
        ]
      ]
    );
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  private function getCourseCertificate($courseData) {
    $allCompleted = true;
    foreach ($courseData['package_files'] as $package_file) {
      if($package_file['course_data']['progress'] != 'completed'){
        $allCompleted = false;
        // will leave the foreach loop, if an item is not completed.
        break;
      }
    }
    return $allCompleted ? $this->buildCourseCertificateLink($courseData) : 'N/A';
  }

  private function buildCourseCertificateLink($courseData) {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('wind_lms')->getPath();
    $linkContent = '<img width="26" src="/' . $module_path . '/img/certificate_icon.png">';
    $renderedAnchorContent = render($linkContent);

    if($courseData['type'] == 'tincan'){
      if(!isset($courseData['statement'])){
        return '';
      }

      $url = Url::fromUserInput(
        '/certificate/' . $courseData['statement']->get('statement_id')->value,
        [
          'attributes' => ['target' => '_blank'],
        ]
      );
      return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
    }

    if($courseData['type'] == 'scorm'){
      $user = $this->currentUser();
      $url = Url::fromUserInput('/cert/' . $courseData['nid'] . '/user/' . $user->id(),
        [
          'attributes' => ['target' => '_blank'],
        ]
      );
      return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
    }
  }
}
