<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\wind_lms\WindLMSNotificationService;
use Drupal\wind_notify\WindNotifyUserService;

class WindLMSAdminSendUserTestEmailForm extends FormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_admin_send_user_test_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $s = WindNotifyUserService::getReplyToEmailAddress();
    $b = '';

    $form['user_uid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Uid:'),
      '#ajax' => [
        'callback' => '::myAjaxCallback', // don't forget :: when calling a class method.
        //'callback' => [$this, 'myAjaxCallback'], //alternative notation
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'keyup',
        'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ]
    ];

    // Create a textbox that will be updated
    // when the user selects an item from the select box above.
    $form['output'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="edit-output">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Send Email',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary']
      )
    ];
    return $form;
  }

  // Get the value from example select field and fill
  // the textbox with the selected text.
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    $markup = 'Enter User Uid';

    if ($textValue = $form_state->getValue('user_uid')) {
      $user = User::load($textValue);

      if (!$user) {
        return ['#markup' => "<div id='edit-output'>Unable to load user.</div>"];
      }

      $params = WindLMSNotificationService::createNotificationParams($user, WindLMSNotificationService::WIND_LMS_USER_COURSE_COMPLETION_REMINDER);
      $subject = WindNotifyUserService::translateTokenizedString($user, $params['subject']);
      $body = WindNotifyUserService::translateTokenizedString($user, $params['body']);
      return ['#markup' => "<div id='edit-output'>
<h3>Preview</h3>
<h4>Subject:</h4>
<p>" . $subject . "</p>
<h4>Body:</h4>
<p>" . $body . "</p>
</div>"];
    }

    // Don't forget to wrap your markup in a div with the #edit-output id
    // or the callback won't be able to find this target when it's called
    // more than once.
    $output = "<div id='edit-output'>$markup</div>";

    // Return the HTML markup we built above in a render array.
    return ['#markup' => $output];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('user_uid')) < 1) {
      $form_state->setErrorByName('user_uid', $this->t('The User Uid field cannot be empty.'));
    }

    if ($textValue = $form_state->getValue('user_uid')) {
      $user = User::load($textValue);
      if (!$user) {
        $form_state->setErrorByName('user_uid', $this->t('Unable to load user. Please check User Id and try again.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($textValue = $form_state->getValue('user_uid')) {
      $user = User::load($textValue);
      if (!$user) {
        return;
      }
      $params = WindLMSNotificationService::createNotificationParams($user, WindLMSNotificationService::WIND_LMS_USER_COURSE_COMPLETION_REMINDER);
      WindNotifyUserService::createNotificationWithParams($user, WindLMSNotificationService::WIND_LMS_USER_COURSE_COMPLETION_REMINDER, $params);
    }
  }

}
