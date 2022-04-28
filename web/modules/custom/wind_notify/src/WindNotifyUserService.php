<?php

namespace Drupal\wind_notify;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Plugin\views\argument\Taxonomy;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class WindNotifyUserService.
 */
class WindNotifyUserService {

  const USER_NEVER_CHECK_IN =  'user-never-check-in-01';
  const USER_ONE_WEEK_CHECK_IN_ID = 'user-one-week-check-in-01';
  const USER_TWO_WEEKS_CHECK_IN_ID = 'user-two-weeks-check-in-01';
  const USER_THEE_DAYS_CHECK_IN_ID = 'user-three-days-check-in-01';

  const USER_ONE_WEEK_COURSE_COMPLETION_REMINDER = 'user-one-week-course-completion-reminder-01';

  /**
   * The database connection used to check the IP against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  static public function sendCheckInReminder(){
    $results = self::getAllUsersOneWeekCheckIn();
    $users = User::loadMultiple($results);
    foreach ($users as $user) {
      // Check if notification record has already been sent out.
      $notifyResults = self::getNotification($user, self::USER_ONE_WEEK_CHECK_IN_ID);
      if (empty($notifyResults)) {
        self::createNotification($user, self::USER_ONE_WEEK_CHECK_IN_ID);
      } else {
        $notiNodes = Node::loadMultiple($notifyResults);
        $oDate = new DrupalDateTime('now');
        $oDate->modify('-7 day');
        $lastWeekTimeStamp = $oDate->format('U');
        $recentNotifications = [];
        foreach ($notiNodes as $notiNode) {
          if ($notiNode->getCreatedTime() >= $lastWeekTimeStamp) {
            // Collect notification that was created less than or equal to 1 week from now
            $recentNotifications[] = $notiNode;
          }
        }
        // If there has NOT been any recent notification in less than a week
        if (empty($recentNotifications)) {
          self::createNotification($user, self::USER_ONE_WEEK_CHECK_IN_ID);
        }
      }
    }

//    $results = self::getAllUsersNeverCheckIn();
//    $users = User::loadMultiple($results);
//    foreach ($users as $user) {
//      $notifyResult = self::getNotification($user, self::USER_NEVER_CHECK_IN);
//      if (empty($notifyResult)) {
//
//      }
//    }
  }

  static public function createNotification(User $user, $field_notification_id){
    $title = self::getNotifyTitle($user, $field_notification_id);
    $body = self::getNotifyBody($user, $field_notification_id);
    $notiNode = Node::create([
      'type' => 'notification',
      'status' => TRUE,
      'title' => $title,
    ]);
    $notiNode->field_user[] = $user->id();
    $notiNode->set('field_notification_id', $field_notification_id);
    $notiNode->set('body', ['value' => $body, 'format' => 'basic_html']);
    try {
      $notiNode->save();
    } catch (EntityStorageException $e) {

    }
    $to = $user->get('mail')->value;
    $replyToEmail = self::getReplyToEmailAddress();
    WindNotifyMailService::sendMail($to, $title, $body, $replyToEmail);
  }

  static public function createNotificationWithParams(User $user, $field_notification_id, $params){
    $title = self::translateTokenizedString($user, $params['subject']);
    $body = self::translateTokenizedString($user, $params['body']);
    $notiNode = Node::create([
      'type' => 'notification',
      'status' => TRUE,
      'title' => $title,
    ]);
    $notiNode->field_user[] = $user->id();
    $notiNode->set('field_notification_id', $field_notification_id);
    $notiNode->set('body', ['value' => $body, 'format' => 'basic_html']);
    try {
      $notiNode->save();
    } catch (EntityStorageException $e) {

    }
    $to = $user->get('mail')->value;
    $replyToEmail = self::getReplyToEmailAddress();
    WindNotifyMailService::sendMail($to, $title, $body, $replyToEmail);
  }

  static public function translateTokenizedString(User $user, string $string) {
    $greeting = self::getGreetingTime();
    $string = str_replace('[date:greeting-time]', $greeting, $string);
    $site_name = \Drupal::config('system.site')->get('name');
    $string = str_replace('[site:name]', $site_name, $string);
    $siteAddress = _wind_lms_get_scheme_and_http_host() . '?destination=/dashboard';
    $string = str_replace('[site:url]', $siteAddress, $string);
    $string = str_replace('[site:login-url]', _wind_gen_button_for_email('Login', $siteAddress), $string);
    $user_full_name = _wind_lms_get_user_full_name($user);
    $string = str_replace('[user:full-name]', $user_full_name, $string);
    $string = str_replace('[user:account-name]', $user->getAccountName(), $string);
    $string = str_replace('[user:mail]', $user->getEmail(), $string);

    return $string;
  }

  static public function getNotifyTitle(User $user, $field_notification_id) {
    switch ($field_notification_id) {
      case self::USER_ONE_WEEK_CHECK_IN_ID :
        return 'One Week Check-In Notice';
        break;
      case self::USER_NEVER_CHECK_IN :
        return 'Your account is untouched';
        break;
      case self::USER_ONE_WEEK_COURSE_COMPLETION_REMINDER :
        return 'Incomplete Course Reminder';
        break;
    }
  }

  static public function getNotifyBody(User $user, $field_notification_id) {
    $site_name = \Drupal::config('system.site')->get('name');
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . self::getGreetingTime() . ' ' . $user_full_name . ', </b><br /></p>';
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $debugInfo = '<p><!-- $field_notification_id: ' . $field_notification_id . '- User Id: ' . $user->id() . ' --></p>';
    $siteAddress = _wind_lms_get_scheme_and_http_host() . '?destination=/dashboard';

    switch ($field_notification_id) {
      case self::USER_ONE_WEEK_CHECK_IN_ID :
        $link = '<p>' . _wind_gen_button_for_email('Login', $siteAddress) . '</p>';
        return $greeting . 'It has been one week since you login. Please click on the link below to login: <br /><br />'  . $link . '<br /><br />' . $closingStatment . $debugInfo;
        break;
      case self::USER_ONE_WEEK_COURSE_COMPLETION_REMINDER :
        $link = '<p>' . _wind_gen_button_for_email('Login', $siteAddress) . '</p>';
        return $greeting . 'You have incomplete course(s). Please click on the link below to login to complete your course(s):  <br /><br />'  . $link . '<br /><br />' . $closingStatment . $debugInfo;
        break;
    }

  }

  static function getReplyToEmailAddress() {
    // Get the custom site notification email to use as the from email address
    // if it has been set. @see admin/config/people/accounts -> Notification email address
    $site_mail = \Drupal::config('system.site')->get('mail_notification');
    // If the custom site notification email has not been set, we use the site
    // default for this. @see /admin/config/system/site-information
    if (empty($site_mail)) {
      $site_mail = \Drupal::config('system.site')->get('mail');
    }
    if (empty($site_mail)) {
      $site_mail = ini_get('sendmail_from');
    }

    return $site_mail;
  }

  static private function getGreetingTime() {
    $Hour = date('G');
    if ( $Hour >= 5 && $Hour <= 11 ) {
      return "Good Morning";
    } else if ( $Hour >= 12 && $Hour <= 18 ) {
      return "Good Afternoon";
    } else if ( $Hour >= 19 || $Hour <= 4 ) {
      return "Good Evening";
    }
  }

  static public function sendMail(User $user, $field_notification_id){

  }

  static private function createUserNotification(User $user, $title, $body) {
    $notiNode = Node::create([
      'type' => 'notification',
      'status' => TRUE,
      'title' => $title,
    ]);
    $notiNode->field_user[] = $user->id();
    $notiNode->set('field_notification_id', 'user-alert-01');
    $notiNode->set('body', ['value' => $body, 'format' => 'basic_html']);
    try {
      $notiNode->save();
    } catch (EntityStorageException $e) {
    }
  }

  /**
   *
   * @param \Drupal\user\Entity\User $user
   */
  static public function sendCcmpletionReminder(User $user){

  }

  static public function getAllUsersOneWeekCheckIn() {
    // Get users that has not login for a 1 weeks.
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-7 day');
    $lastWeekTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0, '>');
    $query->condition('access', $lastWeekTimeStamp, '<');
    return $query->execute();
  }

  static public function getAllUsersTwoWeeksCheckIn() {
    // Get users that has login at least once, but not in 2 weeks.
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-14 day');
    $TwoWeeksAgoTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0, '>');
    $query->condition('access', $TwoWeeksAgoTimeStamp, '<');
    return $query->execute();
  }

  static public function getAllUsersNeverCheckIn() {
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0);
    return $query->execute();
  }

  static public function getAllUsersThreeDaysCheckIn() {
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-3 day');
    $threeDaysAgoTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0, '>');
    $query->condition('access', $threeDaysAgoTimeStamp, '<');
    $query->condition('status', 1);
    return $query->execute();
  }

  static public function getAllUsersYesterday() {
    // Get users that has login at least once, but not in the last 24 hours.
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-1 day');
    $yesterdayTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
    $query->condition('access', 0, '>');
    $query->condition('access', $yesterdayTimeStamp, '<');
    return $query->execute();
  }

  static public function getNotification(User $user, $field_notification_id) {
    $userUid = $user->id();
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'notification');
    $query->condition('field_notification_id', $field_notification_id);
    $query->condition('field_user', $userUid);
    $query->condition('status', 1);
    return $query->execute();
  }

}
