<?php

namespace Drupal\wind_notify\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WindNotifyAPIController extends ControllerBase {

  public function getCurrentUserAlert() {
    $userUid = $this->currentUser()->id();
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'notification');
    $query->condition('field_notification_id', 'user-alert-01');
    $query->condition('field_user', $userUid);
    $query->condition('status', 1);
    $result = $query->execute();
    if(!$result){
      return new JsonResponse([]);
    }

    $data = [];
    $nodes = Node::loadMultiple($result);
    foreach ($nodes as $node) {
      $data[] = $this->getAlert($node);
    }
    return new JsonResponse($data);
  }

  public function setUnpublish(Request $request, Node $notification) {
    $userUid = $this->currentUser()->id();
    $field_userValue = $notification->get('field_user')->getValue();
    if(empty($field_userValue)){
      return new JsonResponse(0);
    }
    $target_ids = array_column($field_userValue, 'target_id');
    // If this notification belongs to current user
    if (in_array($userUid, $target_ids)) {
      $notification->setUnpublished();
      try {
        $notification->save();
      } catch (Exception $exception) {
        return new JsonResponse(0);
      }
      return new JsonResponse(1);
    }
  }

  private function getAlert(Node $node) {
    return [
      'title' => $node->label(),
      'nid' => $node->id(),
      'uuid' => $node->uuid(),
      'type' => $node->getType(),
      'status' => $node->isPublished(),
      'body' => $node->body->value,
      'field_notification_id' => $node->get('field_notification_id')->getValue(),
    ];
  }

}
