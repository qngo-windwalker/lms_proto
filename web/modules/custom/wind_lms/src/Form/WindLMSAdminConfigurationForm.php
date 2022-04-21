<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('wind_lms.settings')
      ->set('hide_certificate_column', $form_state->getValue('hide_certificate_column'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
