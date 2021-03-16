<?php

namespace Drupal\wind\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;

class WindTechnicalSupportForm extends FormBase{

  private $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_technical_support_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;
    $form['subject'] = [
      '#type' => 'select',
      '#title' => $this->t('Subject'),
      '#options' => array(
        'Unable to upload certificate' => 'Unable to upload certificate',
        'Unable to open course' => 'Unable to open course',
        'Course progress not saving' => 'Course progress not saving',
        'Other' => 'Other',
      ),
      '#attributes' => array(
        'class' => ['w-100', 'd-block'] // Bootstrap display block class
      )
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t("If you are having problem with a course, please include the course's name in your description."),
      '#required' => true,
      '#attributes' => array(
        'class' => ['w-100']
      )
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit Ticket',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary', 'mr-3', 'mt-3']
      )
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mt-3']
      )
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = \Drupal::messenger();

    $result = $this->sendEmail($form_state->getValue('subject'), $form_state->getValue('description'));
    if ($result) {
      $messenger->addMessage(t('Your ticket has been submitted.', []), $messenger::TYPE_STATUS);
    } else {
      $messenger->addMessage(t('There was an error. Please try again.', []), $messenger::TYPE_ERROR);
    }

    $destination = \Drupal::request()->query->get('destination');
    if ($destination) {
      $form_state->setRedirectUrl(Url::fromUserInput($destination));
    } else {
      if ($result) {
        $form_state->setRedirectUrl(Url::fromUserInput('/dashboard'));
      } else {
        // If fail, send user back to the form.
        $form_state->setRedirectUrl(Url::fromUserInput('/technical-support'));
      }
    }
  }

  private function sendEmail($subject, $desc) {
    $site_name = \Drupal::config('system.site')->get('name');
    $site_mail = \Drupal::config('system.site')->get('mail');
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal\user\Entity\User::load($this->currentUser()->id());
    $user_full_name = _wind_lms_get_user_full_name($user);
    $greeting = '<p><b>' . _wind_get_greeting_time() . ' ' . $site_mail . ', </b><br /></p>';
//    $closingStatment = '<p>Sincerely,<br /> ' . $site_name . ' team</p>';
    $mailManager = \Drupal::service('plugin.manager.mail');
    $to = $site_mail;
    $from = $user->getEmail();
    $params['to'] = $to;
    $params['subject'] = 'Technical Support New Ticket';
    $params['from_name'] = $site_mail;
    $params['to_name'] = $site_name;
    $params['Reply-To'] = $from;
    $params['message'] = 'Technical Support New Ticket';
    $params['body'] = $greeting;
    $params['body'] .= '<p>Subject: '  . Html::escape($subject) . '<br /></p>';
    $params['body'] .= '<p>Description: '  . Html::escape($desc) . '<br /></p>';
    $params['body'] .= '<p>User Full Name: '  . $user_full_name . '<br /></p>';
    $params['body'] .= '<p>User Uid: '  . $user->id() . '<br /></p>';
    $params['body'] .= '<p>User Email: '  . $user->getEmail() . '<br /></p>';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    // Note: 1st param module name needed so MailManager will invoke hook_mail (!!this hook is required !!!)
    return $mailManager->mail('wind_lms', $params['subject'] , $params['to'], $langcode, $params, $params['reply_to']);
  }
}
