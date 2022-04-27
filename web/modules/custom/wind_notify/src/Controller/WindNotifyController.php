<?php

namespace Drupal\wind_notify\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Datetime\DrupalDateTime;

class WindNotifyController extends ControllerBase {

  public function getUserLastAccess() {
    $schemaAndHost = \Drupal::request()->getSchemeAndHttpHost();
    var_dump($schemaAndHost);
    var_dump($_SERVER);

    // Get users never accessed the site
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0);
    $results = $query->execute();
    $users = User::loadMultiple($results);

    // Get users that has login at least once, but not in the last 24 hours.
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-1 day');
    $yesterdayTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0, '>');
    $query->condition('access', $yesterdayTimeStamp, '<');
    $results = $query->execute();
    $users = User::loadMultiple($results);

//    $query = \Drupal::entityQuery('user');
//    $group = $query
//      ->orConditionGroup()
//      ->condition('created', '1262304000', '<')     // Jan 1, 2010
//      ->condition('created', '1577836800', '>');    // Jan 1, 2020
//    $results = $query->condition($group)
//      ->condition('status', 1)
//      ->sort('created', DESC)
//      ->execute();

    return new JsonResponse([]);
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
