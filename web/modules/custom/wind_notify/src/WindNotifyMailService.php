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
use Drupal\wind_notify\WindNotifyUserService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class WindNotifyMailService.
 */
class WindNotifyMailService {

  /**
   * The database connection used to check the IP against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  static public function sendMail($to, $subject, $body, $reply_to){
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    $mailManager = \Drupal::service('plugin.manager.mail');
    $params['to'] = $to;
    $params['subject'] = $subject;
    $params['from_name'] = $site_mail;
    $params['to_name'] = $site_name;
    $params['reply_to'] = $reply_to;
    $params['message'] = $subject;
    $params['body'] = $body;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', $subject, $to, $langcode, $params, $site_mail);
    if ($result['result'] !== TRUE) {
//      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent.');
    } else {
//      \Drupal::messenger()->addMessage("An enrollment notification  email has been send to {$to}.");
    }
  }

}
