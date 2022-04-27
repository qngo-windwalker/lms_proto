<?php

namespace Drupal\wind_notify\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\wind_notify\WindNotifyUserService;

class WindNotifyUserEdit extends FormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_notify_user_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, User $user = NULL) {

    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;

    $infoMarkup = "<h6>Mail</h6>{$user->get('mail')->value}";
    $infoMarkup .= "<h6>getLastLoginTime()</h6>{$this->whenIsThis($user->getLastLoginTime())}";
    $infoMarkup .= "<h6>getLastAccessedTime</h6>{$this->whenIsThis($user->getLastAccessedTime())}";
    $form['info'] = [
      '#markup' => $infoMarkup
    ];

    $form['user_uid'] = [
      '#type' => 'value',
      '#value' => $user->id(),
    ];

    $form['notification_humanreadable_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification Id'),
      '#description' => $this->t('Notificate Id must match Notification Node::field_notification_id.'),
      '#options' => array(
        0 => '-- Select --',
        WindNotifyUserService::USER_ONE_WEEK_COURSE_COMPLETION_REMINDER => 'USER_ONE_WEEK_COURSE_COMPLETION_REMINDER',
        WindNotifyUserService::USER_ONE_WEEK_CHECK_IN_ID => 'USER_ONE_WEEK_CHECK_IN_ID',
        WindNotifyUserService::USER_NEVER_CHECK_IN => 'USER_NEVER_CHECK_IN',
      ),
      '#ajax' => [
        'callback' => '::previewAjaxCallback', // don't forget :: when calling a class method.
        //'callback' => [$this, 'previewAjaxCallback'], //alternative notation
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'preview-output', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ]
    ];

    $oDate = new DrupalDateTime($user->getLastLoginTime());
    $form['user_last_access'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Last Login Time: ') . $user->getLastLoginTime() . ' - ' . $this->whenIsThis($user->getLastLoginTime()),
      '#description' => 'Modify user access date. Example: -14 day' ,
      '#ajax' => [
        'callback' => '::myAjaxCallback', // don't forget :: when calling a class method.
        //'callback' => [$this, 'previewAjaxCallback'], //alternative notation
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'change',
        'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ],
      '#suffix' => '<div id="edit-output"></div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Edit User',
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

    $form['mail_preview'] = [
      '#markup' => '<h3>' . $this->t('Mail Preview') . '</h3>
                    <div id="preview-output"></div>',
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

    $user_uid = $form_state->getValue('user_uid');
    $user = User::load($user_uid);
    $modifier = trim($form_state->getValue('user_last_access'));
    $oDate = new DrupalDateTime('now');
    $oDate->modify($modifier);
    $timestamp = $oDate->format('U');
    try {
      $user->setLastAccessTime($timestamp);
      $user->setLastLoginTime($timestamp);
      $user->save();
    } catch (EntityStorageException $e) {
      $messenger->addMessage(t('Unable to update.', []), $messenger::TYPE_ERROR);
    }

    $messenger->addMessage(t('Update successful.', []), $messenger::TYPE_STATUS);
  }

  private function whenIsThis($timestamp) {
    $humanReadable = date('D d M Y', $timestamp);
    $today = time();
    $difference = $today - $timestamp;
    $count = floor($difference / 86400);  // (60 * 60 * 24)
    $count .= ' day(s) ago';  // (60 * 60 * 24)
    if ($timestamp == 0) {
      $humanReadable = 'Never';
      $count = '';
    }
    return  $humanReadable . ' -- ' . $count;
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

  // Get the value from example select field and fill
  // the textbox with the selected text.
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    $markup = '';

    // Prepare our textfield. Check if the example select field has a selected option.
    if ($selectedText = $form_state->getValue('user_last_access')) {

      $oDate = new DrupalDateTime('now');
      $oDate->modify($selectedText);
      $timestamp = $oDate->format('U');
      $humanTime = $this->whenIsThis($timestamp);
      $markup = "<p>Preview: <strong>$timestamp - $humanTime</strong></p>";
    }

    // Don't forget to wrap your markup in a div with the #edit-output id
    // or the callback won't be able to find this target when it's called
    // more than once.
    $output = "<div id='edit-output'>$markup</div>";

    // Return the HTML markup we built above in a render array.
    return ['#markup' => $output];
  }

  // Get the value from example select field and fill
  // the textbox with the selected text.
  public function previewAjaxCallback(array &$form, FormStateInterface $form_state) {
    $markup = 'Nothing selected';

    // Prepare our textfield. Check if the example select field has a selected option.
    if ($selectedValue = $form_state->getValue('notification_humanreadable_id')) {
      $user_uid = $form_state->getValue('user_uid');
      $user = User::load($user_uid);
      // Place the text of the selected option in our textfield.
      $markup = WindNotifyUserService::getNotifyBody($user, $selectedValue);
    }

    // Don't forget to wrap your markup in a div with the #edit-output id
    // or the callback won't be able to find this target when it's called
    // more than once.
    $output = "<div id='preview-output'>$markup</div>";

    // Return the HTML markup we built above in a render array.
    return ['#markup' => $output];
  }
}
