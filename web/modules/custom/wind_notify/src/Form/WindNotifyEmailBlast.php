<?php

namespace Drupal\wind_notify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

class WindNotifyEmailBlast extends FormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_notify_email_blast';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL) {

//    $courseFileHelper = new CourseFileHelper($node_course);
    $form['#tree'] = TRUE;
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['notification_humanreadable_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Notification Id'),
      '#description' => $this->t('Notificate Id must match Notification Node::field_notification_id.'),
      '#required' => TRUE,
    ];

    $form['user_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('User IDs'),
      '#description' => $this->t('Comma separated list'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
      '#attributes' => array(
        'class' => ['button--primary']

      )
    ];
    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mr-1']
      )
    ];
    return $form;
  }

  /**
   * @see \Drupal\image\Controller\QuickEditImageController->upload() to see how to implement Ajax.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = \Drupal::messenger();

    $userUids = trim($form_state->getValue('user_ids'));
    $userUids = explode(',', $userUids);
    $notification_id = $form_state->getValue('notification_humanreadable_id');
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'notification');
    $query->condition('status', 1);
    $query->condition('field_notification_id', $notification_id);
    $result = $query->execute();

    if(!$result) {
      $messenger->addMessage(t('Unable to locate notification node template.', []), $messenger::TYPE_ERROR);
      return;
    }

    $node = \Drupal\node\Entity\Node::load(array_shift($result));
//        drupal_write_record('files', $file);
//        file_save_data();
    $users = User::loadMultiple($userUids);
    foreach ($users as $user) {
      $this->sendEmail($user, $node);
    }
  }

  /**
   * Search for token and replace it when real data
   * @param string $text
   * @return string
   */
  private function applyTokenReplacement(Node $node, User $user){
    $text = $node->get('body')->value;
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $user_full_name = _wind_lms_get_user_full_name($user);
//    $text = '<p><b>' .  $user_full_name . ', </b><br /></p>';
    $debugInfo = '<!--  Course Id: ' . $node->id() . '- User Id: ' . $user->id() . ' -->';
    $greeting = '<p><b>' . $this->getGreetingTime() . ' ' . $user_full_name . ', </b><br /></p>';
    $greeting . 'A new training course is available to you. Please click on the link below to login and take the course: <br /><br /> '  . ' ' . '<br /><br />' . $closingStatment ;
    $greeting . 'A new training course is available to you. Please click on the link below to login and take the course: <br /><br /> '  . ' ' . '<br /><br />' . $closingStatment ;;
    return $text . $debugInfo;
  }

  private function sendEmail(User $user, Node $node) {
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    $subject = $node->label();
    $body = $this->applyTokenReplacement($node->get('body')->value, $user);
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $user->get('mail')->value;
    $params['to'] = $to;
    $params['to_name'] = $site_name;
    $params['from_name'] = $site_mail;
    $params['reply_to'] = $site_mail;
    $params['subject'] = $subject;
    $params['message'] = $body;
    $params['node_title'] = $subject ;
    $params['body'] = $body;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    $result = $mailManager->mail('wind_lms', $subject, $to, $langcode, $params, $site_mail);
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
