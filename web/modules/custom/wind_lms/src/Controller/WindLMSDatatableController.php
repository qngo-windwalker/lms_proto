<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\wind_lms\WindLMSJSONStructure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

class WindLMSDatatableController extends ControllerBase {

  public function client() {
    $collection = [];
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'client')
      ->execute();

    if ($result) {
      $nodes = Node::loadMultiple($result);
      $destination = $this->getDestinationOfReferer();
      foreach ($nodes as $nid => $node) {
        $courseTotal = $node->get('field_course')->isEmpty() ? '0' : count($node->get('field_course')->getValue());
//                $field = $node->get('field_user_last_name')->isEmpty() ? '' : $user_account->get('field_user_last_name')->value;
//                $progress = $this->getUserProgress($node);
        $viewLink = Link::fromTextAndUrl(
          $node->getTitle(),
          Url::fromRoute(
            "ww_connector.client_view",
            [
              'node_client' => $node->id(),
            ]
          )
        );

        $operartions = '<div class="btn-group">';
        $operartions .= '<a class="btn btn-sm btn-outline-light " href="client/' . $nid . '">View</a>';
        $operartions .=  $this->getNodeEditLink($nid, $this->getDestinationOfReferer())->toString();
        $operartions .= '<a class="btn btn-sm btn-outline-light anchor-info" data-nid="'. $nid. '" href="#info" alt="Quick Information"><i class="fas fa-ellipsis-h"></i></a>';
        $operartions .= '</div>';
        $infoHTML =  $this->link(t('Delete'), "internal:/node/{$nid}/delete", $destination)->toString();
        $collection[] = [
          $viewLink->toString(),
          $courseTotal,
          'status' => $this->getStatusHTML($node),
          'operations' => $operartions,
          'nid' => $nid,
          'rowNid' => 'nid-' . $nid,
          'expiration' => '12/12/2020',
          'infoHTML' => $infoHTML,
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  public function getCurriculum() {
    $collection = [];
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'curriculum')
      ->execute();

    if ($result) {
      $nodes = Node::loadMultiple($result);
      $destination = $this->getDestinationOfReferer();
      foreach ($nodes as $nid => $node) {
        $courseTotal = $node->get('field_course')->isEmpty() ? '0' : count($node->get('field_course')->getValue());
        //                $field = $node->get('field_user_last_name')->isEmpty() ? '' : $user_account->get('field_user_last_name')->value;
        //                $progress = $this->getUserProgress($node);
//        $viewLink = Link::fromTextAndUrl(
//          $node->getTitle(),
//          Url::fromRoute(
//            "node.client_view",
//            [
//              'node_client' => $node->id(),
//            ]
//          )
//        );

        $operartions = '<div class="btn-group">';
        $operartions .= '<a class="btn btn-sm btn-outline-secondary" href="node/' . $nid . '">View</a>';
        $operartions .=  $this->getNodeEditLink($nid, $this->getDestinationOfReferer())->toString();
        $operartions .= '<a class="btn btn-sm  btn-outline-secondary anchor-info" data-nid="'. $nid. '" href="#info" alt="Quick Information"><i class="fas fa-ellipsis-h"></i></a>';
        $operartions .= '</div>';
        $infoHTML =  $this->link(t('Delete'), "internal:/node/{$nid}/delete", $destination)->toString();
        $collection[] = [
          'title' => $node->label(),
          '',
          'course' => $courseTotal,
          'status' => $this->getStatusHTML($node),
          'action' => $operartions,
          'nid' => $nid,
          'rowNid' => 'nid-' . $nid,
          'expiration' => '12/12/2020',
          'infoHTML' => $infoHTML,
          'course_data' => $this->getCoursesJsonData($node->get('field_course')->referencedEntities()),
          'DT_RowId' => "row-nid-" . $nid, // Datatable <tr /> id
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  /**
   * wind_lms.datatable.courses:
   *  path: 'wl-datatable/courses'
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getCourses() {
    $collection = [];
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'course')
      ->execute();

    if ($result) {
      $nodes = Node::loadMultiple($result);
      $destination = $this->getDestinationOfReferer();
      /**
       * @var Number $nid
       * @var  NodeInterface $node
       */
      foreach ($nodes as $nid => $node) {
//        $courseTotal = $node->get('field_course')->isEmpty() ? '0' : count($node->get('field_course')->getValue());
        //                $field = $node->get('field_user_last_name')->isEmpty() ? '' : $user_account->get('field_user_last_name')->value;
        //                $progress = $this->getUserProgress($node);
        //        $viewLink = Link::fromTextAndUrl(
        //          $node->getTitle(),
        //          Url::fromRoute(
        //            "node.client_view",
        //            [
        //              'node_client' => $node->id(),
        //            ]
        //          )
        //        );

        $operartions = '<div class="btn-group">';
        $operartions .= '<a class="btn btn-sm btn-outline-secondary" href="node/' . $nid . '">View</a>';
        $operartions .=  $this->getNodeEditLink($nid, $this->getDestinationOfReferer())->toString();
        $operartions .= '<a class="btn btn-sm  btn-outline-secondary anchor-info" data-nid="'. $nid. '" href="#info" alt="Quick Information"><i class="fas fa-ellipsis-h"></i></a>';
        $operartions .= '</div>';
        $infoHTML =  $this->link(t('Delete'), "internal:/node/{$nid}/delete", $destination)->toString();
        $collection[] = [
          'title' => $node->label(),
          '',
          'status' => $node->isPublished(),
          'action' => $operartions,
          'nid' => $nid,
          'rowNid' => 'nid-' . $nid,
          'field_learner_access' => $node->get('field_learner_access')->getString(),
          'field_user_team' => $this->getCourseFieldUserTeam($node),
          'learners_data' => $this->getLearnersJsonData($node->get('field_learner')->referencedEntities()),
          'courses_data' => $this->getCourseJsonData($node->get('field_package_file')->referencedEntities()),
          'category_data' => $this->getCategoryJsonData($node->get('field_category')->referencedEntities()),
          'infoHTML' => $infoHTML,
          'DT_RowId' => "row-nid-" . $nid, // Datatable <tr /> id
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }


  private function getCourseFieldUserTeam(Node $course) {
    $field_user_team = $course->field_user_team->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $term */
    return array_map(function($term){
      return [
        'tid' => $term->id(),
        'label' => $term->label(),
        'vid' => $term->get('vid')->getString(),
        'user' => $this->getUserByUserTeamTid(array($term->id()), $term->get('vid')->getString())
      ];
    }, $field_user_team);
  }

  private function getUserByUserTeamTid($tids) {
    $userStorage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery();
    $result = $query
      ->condition('field_team', $tids, 'IN')
      ->condition('status', '1')
      ->execute();

    if ($result) {
      $collection = [];
      // Have remove the key from array so JsonResponse() convert it to array instead of object
      foreach ($result as $uid){
        $collection[] = ['uid' => $uid];
      }
      return $collection;
    }

    return [];
  }

  private function getCoursesJsonData($arr) {
    return array_map(function(NodeInterface $node){
      return [
        'title' => $node->label(),
      ];
    }, $arr);
  }

  private function getLearnersJsonData($arr) {
    return array_map(function(User $user){
      return [
        'username' => $user->label(),
        'full_name' => _wind_lms_get_user_full_name($user),
        'uid' => $user->id(),
        'email' => $user->getEmail()
      ];
    }, $arr);
  }

  private function getCategoryJsonData($arr) {
    return array_map(function(\Drupal\taxonomy\Entity\Term $term){
      return [
        'tid' => $term->id(),
        'label' => $term->label()
      ];
    }, $arr);
  }

  private function getCourseJsonData($arr) {
    return array_map(function(\Drupal\file\Entity\File $file){
      $output = [
        'title' => '',
        'fid' => $file->id(),
      ];

      $scorm = _wind_scorm_load_by_fid($file->id());
      if ($scorm) {
        $output['title'] = _wind_lms_get_scorm_package_title($scorm->id);
      }
      return $output;
    }, $arr);
  }


  /**
   * @param NodeInterface $node
   * @return string
   */
  private function getStatusHTML(NodeInterface $node) {
    return $node->isPublished() ? '<span class="text-success">&#9679; Active</span>' : '<span class="text-danger">&#9679; Inactive</span>';
  }

  /**
   * When using  \Drupal::service('path.current')->getPath(); it returns
   * .../datatable/... We want the page that ajax-ing (GET) the datatable/..
   * URL
   * @return string
   */
  private function getDestinationOfReferer() {
    // We want to the URL of the page the ajax-ing this datatable/*
    $referer = \Drupal::request()->server->get('HTTP_REFERER');
    // Get the base URL. Ex 'http://localhost'
    $host = \Drupal::request()->getSchemeAndHttpHost();
    return str_replace($host, '', $referer);
  }

  /**
   * @param $nid
   * @param $destination
   * @return \Drupal\core\Link
   */
  private function getNodeEditLink($nid, $destination) {
    return Link::fromTextAndUrl(
      t('Edit'),
      Url::fromUri(
        "internal:/node/{$nid}/edit",
        [
          'attributes' => ['class' => 'btn btn-sm btn-outline-secondary'],
          'query' => ['destination' => $destination],
        ]
      )
    );
  }

  /**
   * Construct an anchor <a>.
   *
   * @param $name
   * @param $fromUri
   * @param $destination
   * @return Link
   */
  private function link($name, $fromUri, $destination) {
    return Link::fromTextAndUrl(
      $name,
      Url::fromUri(
        $fromUri,
        [
          'attributes' => ['class' => 'btn btn-sm btn-outline-light'],
          'query' => ['destination' => $destination],
        ]
      )
    );
  }
}
