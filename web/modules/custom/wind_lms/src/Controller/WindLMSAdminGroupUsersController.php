<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Drupal\Core\Link;
use Drupal\Core\Url;

class WindLMSAdminGroupUsersController extends ControllerBase{

  public function getContent() {
    $rows = array();
    $query = \Drupal::entityQuery('group');
    $result = $query->execute();

    if ($result) {
      foreach ($result as $item) {
        $group = Group::load($item);
        $owner = $group->getOwner();
        $rows[$item] = [
          $item,
          Link::fromTextAndUrl($group->label(), Url::fromUri("internal:/group/{$item}"))->toString(),
          $group->getGroupType()->label(),
          $owner ? $this->getUserLink($owner) : '',
          count($group->getMembers()),
        ];
      }
    }
    $output = '';
    $renderable =  [
      '#type' => 'table',
      '#header' => [
        t('GROUP ID'),
        t('NAME'),
        t('Type'),
        t('Owner'),
        t('Member Total'),
        t('Required Score'),
        t('Attempts'),
        t('Activities'),
        t('Mandatory'),
      ],
      '#rows' => $rows,
      '#attributes' => [
        'class' => ['step_block_table'],
      ],
    ];

    $output .= render($renderable);
    return array(
      'data' => array(
        '#markup' => $output
      )
    );

  }

  private function getUserLink(User $user) {
    return Link::fromTextAndUrl(
      $user->getUsername(),
      Url::fromUri("internal:/user/{$user->id()}")
    )->toString();
  }
}
