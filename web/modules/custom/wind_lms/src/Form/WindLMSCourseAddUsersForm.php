<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Element\StatusMessages;
use Symfony\Component\HttpFoundation\JsonResponse;

class WindLMSCourseAddUsersForm extends FormBase{
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_course_adduser';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL) {

//    $courseFileHelper = new CourseFileHelper($node_course);
    $form['#tree'] = TRUE;
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Type the email address for an existing user or add new users.'),
    ];
    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mr-1']
      )
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save'
    ];
    return $form;
  }

  /**
   * @see \Drupal\image\Controller\QuickEditImageController->upload() to see how to implement Ajax.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $destination = $form['upload_container']['js_file']['#upload_location'];
    if (isset($destination) && !file_prepare_directory($destination, FILE_CREATE_DIRECTORY)) {
//            return new JsonResponse(['main_error' => $this->t('The destination directory could not be created.'), 'errors' => '']);
      \Drupal::logger('ww_connector')->error('The destination directory could not be created');
      \Drupal::messenger()->addError(t('The file could not be uploaded because the destination %destination is invalid.', ['%destination' => $destination]));
      return;
    }

    $validators = ['file_validate_extensions' => ['js']];
    // Attempt to save the file
    // @see \Drupal\image\Controller\QuickEditImageController->upload()
    $result = file_save_upload('upload_container', $validators, $destination);
    if (is_array($result) && $result[0]) {
      /** @var \Drupal\file\Entity\File $file */
      $file = $result[0];

      \Drupal::messenger()->addStatus(t('The file has successfully uploaded to destination: %destination.', ['%destination' => $destination]));
    } else {
      \Drupal::messenger()->addError(t('The file could not be uploaded because the destination: %destination is invalid.', ['%destination' => $destination]));
      // "main_error", which is displayed inside the dropzone area.
      $messages = StatusMessages::renderMessages('error');

      return \Drupal::service('renderer')->render($messages);
//            $this->renderer->render($messages);
      return;
      // Return a JSON object containing the errors from Drupal and our
      return new JsonResponse(['errors' => $this->renderer->render($messages), 'main_error' => $this->t('The image failed validation.')]);
    }
//        $file->status = FILE_STATUS_PERMANENT;
//        drupal_write_record('files', $file);
//        file_save_data();
  }
}
