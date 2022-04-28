<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wind_lms\WindLMSNotificationService;

/**
 * Provides a form for User Statistics Settings.
 */
class WindLMSAdminConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wind_lms.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wind_lms.settings');

    $form['my_training_section'] = [
      '#type' => 'details',
      '#title' => $this->t('My Training Section'),
      '#open' => TRUE,
    ];

    $form['my_training_section']['hide_certificate_column'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide Certificate column'),
      '#description' => $this->t('If checked Certificate column will be hidden on My Training table.'),
      '#default_value' => $config->get('hide_certificate_column'),
    ];

    $form['notification_config'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Email Notification Settings'),
      '#default_tab' => 'edit-visibility',
    ];

    $form['notification'] = [
      '#type' => 'details',
      '#title' => $this->t('1 Week course Completion'),
      '#description' => $this->t('Edit the 1 week course completion reminder email message sent to users if they have not completed their assigned course(s).
                                  Available variables are: [site:name], [site:url], [user:full-name], [user:account-name], [user:mail], [site:login-url].'),
      '#group' => 'notification_config',
      '#parents' => ['settings', 'notification'],
    ];

    $form['notification']['one_week_course_completion_reminder_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => WindLMSNotificationService::getNotificationSettings($config, 'one_week_course_completion_reminder.subject'),
      '#maxlength' => 180,
    ];

    $form['notification']['one_week_course_completion_reminder_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => WindLMSNotificationService::getNotificationSettings($config, 'one_week_course_completion_reminder.body'),
      '#rows' => 12,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('wind_lms.settings')
      ->set('hide_certificate_column', $form_state->getValue('hide_certificate_column'))
      ->set('one_week_course_completion_reminder.subject', $form_state->getValue('one_week_course_completion_reminder_subject'))
      ->set('one_week_course_completion_reminder.body', $form_state->getValue('one_week_course_completion_reminder_body'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
