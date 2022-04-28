<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\wind_lms\CourseNode;
use Drupal\wind_notify\WindNotifyMailService;
use Drupal\wind_notify\WindNotifyUserService;

/**
 * Class WindLMSNotificationService.
 */
class WindLMSNotificationService {

  const NOTIFICATION_USER_ALERT = 'user-alert-01';
  const WIND_LMS_USER_COURSE_COMPLETION_REMINDER = 'wind_lms-user-course-completion-reminder-01';

  /**
   * The database connection used to check the IP against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  static public function sendCompletionReminder() {
    $users = self::getCompletionReminderUsers();
    if (empty($users)) {
      return;
    }

    foreach ($users as $user) {
      // Check if notification record has already been sent out.
      $notifyResults = WindNotifyUserService::getNotification($user, self::WIND_LMS_USER_COURSE_COMPLETION_REMINDER);
      $params = self::createNotificationParams($user, self::WIND_LMS_USER_COURSE_COMPLETION_REMINDER);
      if (empty($notifyResults)) {
        WindNotifyUserService::createNotificationWithParams($user, self::WIND_LMS_USER_COURSE_COMPLETION_REMINDER, $params);
      } else {
        // If notification already sent out, check when is the last time it sent out
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
          WindNotifyUserService::createNotificationWithParams($user, self::WIND_LMS_USER_COURSE_COMPLETION_REMINDER, $params);
        }
      }
    }
  }

  static function createNotificationParams(User $user, $field_notification_id) {
    $config = \Drupal::config('wind_lms.settings');
    $subject = '';
    $body = '';

    switch ($field_notification_id) {
      case self::WIND_LMS_USER_COURSE_COMPLETION_REMINDER :
        $subject = self::getNotificationSettings($config, 'one_week_course_completion_reminder.subject');
        $body = self::getNotificationSettings($config, 'one_week_course_completion_reminder.body');;
        break;
    }

    return [
      'subject' => $subject,
      'body' => $body
    ];
  }

  static function getNotificationSettings(\Drupal\Core\Config\Config $config, string $configKey){
    if ($config->get($configKey)) {
      return $config->get($configKey);
    }

    // If no value in the config, get static value
    switch ($configKey) {
      case 'one_week_course_completion_reminder.subject':
        return 'Incomplete Course Reminder';

      case 'one_week_course_completion_reminder.body':
        return 'Good Afternoon [user:full-name],

You have incomplete course(s). Please click on the link below to login to complete your course(s):

[site:login-url]


Sincerely,
[site:name] team
    ';
    }

    return '';
  }

  /**
   * Create new notification node.
   *
   * @param \Drupal\user\Entity\User $user
   * @param $title
   * @param $body
   */
  static private function createUserAlert(User $user, $title, $body) {
    $existingNotify = self::findUserAlert($user, $title, $body);

    // We don't want duplicate notification
    if ($existingNotify) {
      return;
    }

    $notiNode = Node::create([
      'type' => 'notification',
      'status' => TRUE,
      'title' => $title,
    ]);
    $notiNode->field_user[] = $user->id();
    $notiNode->set('field_notification_id', 'user-alert-01');
    $notiNode->set('body', ['value' => $body, 'format' => 'full_html']);
    try {
      $notiNode->save();
    } catch (EntityStorageException $e) {
    }
  }

  static private function findUserAlert(User $user, $title, $body) {
    $userUid = $user->id();
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'notification');
    $query->condition('field_notification_id', self::NOTIFICATION_USER_ALERT);
    $query->condition('field_user', $userUid);
    $query->condition('title', $title);
    $query->condition('body', $body);
    $query->condition('status', 1);
    return $query->execute();
  }

  static public function createNotifition(User $user, $field_notification_id){
    $title = 'Completion Reminder';
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
    WindNotifyMailService::sendMail($to, $title, $body, 'no-reply@windwalker.com');
  }

  static public function getNotifyBody(User $user, $field_notification_id) {
    $site_name = \Drupal::config('system.site')->get('name');
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . self::getGreetingTime() . ' ' . $user_full_name . ', </b><br /></p>';
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $debugInfo = '<p><!-- $field_notification_id: ' . $field_notification_id . '- User Id: ' . $user->id() . ' --></p>';
    $siteAddress = _wind_lms_get_scheme_and_http_host() . '?destination=/my-progress';
    $link = '<p>' . _wind_gen_button_for_email('Login', $siteAddress) . '</p>';
    return $greeting . 'You have uncompleted course(s). Please click on the link below to login to complete your course(s): <br /><br />'  . $link . '<br /><br />' . $closingStatment . $debugInfo;
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

  private static function getCompletionReminderUsers() {
    // Get users that has NOT logged in since last week
    $oDate = new DrupalDateTime('now');
    $oDate->modify('-7 day');
    $threeDaysAgoTimeStamp =  $oDate->format('U');
    $query = \Drupal::entityQuery('user');
    $query->condition('status', 1);
//    $query->condition('access', 0, '>');
//    $query->condition('access', $threeDaysAgoTimeStamp, '<');
    $query->condition('status', 1);
    $result = $query->execute();
    if (empty($result)) {
      return [];
    }

    $collection = [];
    $users = User::loadMultiple($result);
    foreach ($users as $uid => $user) {
      $userAllCourseData = WindLMSJSONStructure::getUserAllCourseData($user);
      foreach ($userAllCourseData as $userCouseData) {
        if (!$userCouseData['isCompleted']) {
          $collection[$uid] = $user;
        }
      }
    }
    return $collection;
  }

}
