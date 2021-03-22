<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class EmailNotification.
 */
class CourseEmailNotification {

  /**
   * Compose email and sent it
   * @param \Drupal\node\NodeInterface $node
   * @param $uid
   */
  public function sendEmail(NodeInterface $node, $uid) {
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal\user\Entity\User::load($uid);
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . $this->getGreetingTime() . ' ' . $user_full_name . ', </b><br /></p>';
    $courseLink = '<p>' . _wind_gen_button_for_email($node->label(),  $_SERVER['HTTP_ORIGIN']  . '?destination=/dashboard') . '</p>';
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $debugInfo = '<p><!-- Course Id: ' . $node->id() . '- User Id: ' . $uid . ' --></p>';
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $user->get('mail')->value;
    $params['to'] = $to;
    $params['subject'] = 'New enrollment';
    $params['from_name'] = $site_mail;
    $params['to_name'] = $site_name;
    $params['reply_to'] = $site_mail;
    $params['message'] = 'New enrollment: ' . $node->label();
    $params['node_title'] = $node->label() ;
    $params['body'] = $greeting . 'A new training course is available to you. Please click on the link below to login and take the course: <br /><br /> '  . $courseLink . '<br /><br />' . $closingStatment . $debugInfo;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', 'New Enrollment', $to, $langcode, $params, $site_mail);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent.');
    } else {
      \Drupal::messenger()->addMessage("An enrollment notification  email has been send to {$to}.");
    }
  }

  private function getGreetingTime() {
    $Hour = date('G');
    if ( $Hour >= 5 && $Hour <= 11 ) {
      return "Good Morning";
    } else if ( $Hour >= 12 && $Hour <= 18 ) {
      return "Good Afternoon";
    } else if ( $Hour >= 19 || $Hour <= 4 ) {
      return "Good Evening";
    }
  }
}
