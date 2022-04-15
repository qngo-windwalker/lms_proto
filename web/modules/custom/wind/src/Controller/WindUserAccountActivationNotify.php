<?php

namespace Drupal\wind\Controller;

use Drupal\user\Entity\User;

class WindUserAccountActivationNotify{

  public function getContent($uid){
    $header = [
      array('data' => 'First Name', 'class' => 'user-first-name-header'),
      array('data' => 'Last Name', 'class' => 'user-last-name-header'),
      array('data' => 'Email', 'class' => 'user-email-header'),
      array('data' => 'Progress', 'class' => 'username-header'),
    ];

    $user = User::load($uid);
    $result = _user_mail_notify('status_activated', $user);

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
    ];
    return array(
      '#type' => 'markup',
    );

  }
}
