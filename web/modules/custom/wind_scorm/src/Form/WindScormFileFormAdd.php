<?php

/**
 * @file
 * Contains \Drupal\my_module\Form\FileFormAdd.
 */

namespace Drupal\wind_scorm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * ..........
 *
 * @todo
 *   sanitation.
 */
class WindScormFileFormAdd extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_scorm_file_upload';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );

    $form['file_upload_details'] = array(
      '#markup' => t('<b>The File</b>'),
    );

    $validators = array(
      'file_validate_extensions' => array('pdf'),
    );
    $form['my_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('PDF format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://my_files/',
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('my_file') == NULL) {
      $form_state->setErrorByName('my_file', $this->t('File.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Need to get file details i.e upload file name, size etc.

    dpm($form_state->getValue('my_file'));

    // Display success message.
    drupal_set_message('AMS file successfully uploaded.');

    // Redirect.
    //    $form_state->setRedirect('my_module._______');
  }

}
