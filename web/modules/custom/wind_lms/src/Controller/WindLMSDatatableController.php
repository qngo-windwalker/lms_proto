<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
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
          'infoHTML' => $infoHTML
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  public function getAllCourseUsers(Group $group) {
    $collection = [];
    $groupMembers = $group->getMembers();
    foreach ($groupMembers as $groupMember) {
      $groupContent = $groupMember->getGroupContent();
      $user = $groupMember->getUser();
      $uid = $user->id();
      $collection[] = [
        'first_name' => $user->get('field_first_name')->getString(),
        'last_name' => $user->get('field_last_name')->getString(),
        'email' => $user->getEmail(),
        'last_login' => $this->formatTime($user->getLastLoginTime()),
        'last_accessed' => $this->formatTime($user->getLastAccessedTime()),
        'operations' => '...',
        'rowUid' => 'uid-' . $uid,
        'uid' => $uid,
      ];
    }
    return new JsonResponse(['data' => $collection]);
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
   * @param NodeInterface $node
   * @return string
   */
  private function getStatusHTML(NodeInterface $node) {
    return $node->isPublished() ? '<span class="text-success">&#9679; Active</span>' : '<span class="text-danger">&#9679; Inactive</span>';
  }

  /**
   * When using  \Drupal::service('path.current')->getPath(); it returns .../datatable/...
   * We want the page that ajax-ing (GET) the datatable/.. URL
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
          'attributes' => ['class' => 'btn btn-sm btn-outline-light'],
          'query' => ['destination' => $destination]
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
          'query' => ['destination' => $destination]
        ]
      )
    );
  }

  /**
   * @param EntityInterface $node_course
   * @return int|string|void
   */
  private function getAllCourseClients(EntityInterface $node_course) {
    $result = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'client')
      ->condition('field_course', $node_course->id())
      ->execute();
    if($result){
      return count($result);
    }
    return '0';
  }
}
