<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Render\Markup;

class WindLMSJsonController extends ControllerBase {
  public function getCurrentUser(){
    $user = $this->currentUser();

    return new JsonResponse([
      'uid' => $user->id(),
      'name' => $user->getAccountName()
    ]);
  }

  public function getCurrentDashboard(){
    $user = $this->currentUser();
    $rows = array();
    $coursesData = _wind_lms_get_user_all_assigned_course_data($user , \Drupal::request()->get('lang'));
    foreach ($coursesData as $courseData) {
      $rows[] = $this->buildCourseRow($courseData);
    }
//    return $rows;
    return new JsonResponse([
      'uid' => $user->id(),
      'name' => $user->getAccountName(),
      'tableRow' => $rows
    ]);
  }

  protected function buildCourseRow($courseData) {
    $title = $courseData['title'];
    $course_folder = $courseData['folder'];
    $TC_COURSE_ID = $courseData['tincan_course_id'];
    $progress = $courseData['progress'];
    $certificateLink = $progress != 'Completed' ? 'N/A' : $this->getCourseCertificate($courseData);
    return [
      'data' => array(
        $this->buildCourseLink($title, $course_folder),
        $progress,
        $certificateLink,
      ),
      'class' => array('course-row'),
      'data-tincan-id' =>$TC_COURSE_ID
    ];
  }

  protected function buildCourseLink($title, $course_folder) {
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
    if(!isset($courseData['statement'])){
      return '';
    }
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('wind_lms')->getPath();
    $linkContent = '<img width="26" src="/' . $module_path . '/img/certificate_icon.png">';
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput('/certificate/' . $courseData['statement']->get('statement_id')->value, ['attributes' => ['target' => '_blank']]);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }
}
