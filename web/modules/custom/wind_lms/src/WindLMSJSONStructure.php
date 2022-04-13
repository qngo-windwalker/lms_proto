<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

/**
 * Class WindLMSJSONStructure.
 */
class WindLMSJSONStructure {

  protected $database;

  /**
   * OpignoScorm constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  static function getUser(User $user) {
    $field_team = $user->field_team->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $teamEntities */
    $teamEntities = array_map (function($term){
      return [
        'tid' => $term->id(),
        'label' => $term->label(),
        'vid' => $term->get('vid')->getString(),
        'ancestors' => self::getUserTeamAncestors($term->id())
      ];
    }, $field_team);

    return [
      'uid' => $user->id(),
      'username' => $user->getAccountName(),
      'name' => $user->getAccountName(),
      'fullName' => _wind_lms_get_user_full_name($user),
      'field_first_name' => $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '',
      'field_last_name' => $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '',
      'status' => $user->get('status')->value,
      'mail' => $user->get('mail')->value,
      'access' => $user->get('access')->value,
      'login' => $user->get('login')->value,
      'field_team' => $teamEntities,
    ];
  }

  static function getCourse($courseData, User $user) {
    $title = $courseData['title'];
    $node = \Drupal\node\Entity\Node::load($courseData['nid']);
    $field_category = $node->field_category->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $teamEntities */
    $categoryEntities = array_map (function($term){
      return [
        'tid' => $term->id(),
        'label' => $term->label(),
        'vid' => $term->get('vid')->getString()
      ];
    }, $field_category);

    return [
      'title' => $courseData['title'],
      'nid' => isset($courseData['nid']) ? $courseData['nid'] : '',
      'type' => $courseData['type'],
      'courseLink' => self::buildCourseLink($title, $courseData),
      'certificateLink' => self::getCourseCertificate($courseData, $user),
      'field_category' => $categoryEntities,
      'package_files' => isset($courseData['package_files']) ? $courseData['package_files'] : [],
      'isCompleted' => \Drupal\wind_lms\CourseNode::isCourseCompleted($courseData),
      'certificateNode' => self::getCertificateNode($courseData['nid'], $user->id())
    ];
  }

  /**
   * @param array $courseData
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\GeneratedLink|string
   */
  public static function getCourseCertificate($courseData, $user) {
    $courseCompleted = \Drupal\wind_lms\CourseNode::isCourseCompleted($courseData);
    return $courseCompleted === true ? self::buildCourseCertificateLink($courseData, $user) : 'N/A';
  }

  public static function buildCourseLink($title, $courseData) {
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
   * Generate Certificate Id that can be decoded for traceability
   * @param array $courseData
   * @param \Drupal\user\Entity\User $user
   *
   * @return \Drupal\Core\GeneratedLink
   */
  public static function buildCourseCertificateLink($courseData, User $user) {
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

  /**
   * @param $courseNid
   * @param $uid
   *
   * @return array
   */
  public static function getCertificateNode($courseNid, $uid) {
    /** @var \Drupal\wind_lms\CertificateNode $certNodeService */
    $certNodeService = \Drupal::service('wind_lms.certifcate_node');
    /** @var \Drupal\node\Entity\Node $certNodeService */
    $certNode = $certNodeService->loadCertNode($courseNid, $uid);
    if (!$certNode) {
      return [];
    }

    return [
      'title' => $certNode->label(),
      'nid' => $certNode->id(),
      'field_completion_verified' => $certNode->get('field_completion_verified')->getString(),
      'field_attachment' => $certNode->get('field_attachment')->getString(),
    ];
  }

  static function getUserTeamAncestors($tid) {
    $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid);
    $list = [];
    foreach ($ancestors as $term) {
      // Don't include ourselve in the tree
      if ($term->id() == $tid) {
        continue;
      }

      $list[] = [
        'tid' => $term->id(),
        'label' => $term->label()
      ];
    }
    // list is from current to ancestors,
    // we flip it so we get oldest to latest.
    return array_reverse($list);
  }
}
