<?php

/**
 * @file
 * Contains \Drupal\my_module\Form\FileFormAdd.
 */

namespace Drupal\wind_scorm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
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

    $validators = array(
      'file_validate_extensions' => array('zip'),
    );
    $form['opigno_scorm_package'] = array(
      '#type' => 'managed_file',
      '#title' => t('SCORM *'),
      '#size' => 20,
      '#description' => t('ZIP format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://opigno_scorm_extracted',
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
   * @see Drupal\opigno_module\Form\AddExternalPackageForm
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation is optional.
//    $file_field = "opigno_scorm_package";
//    $storage = $form_state->getStorage();
//    $is_ppt = (isset($storage['mode']) && $storage['mode'] == 'ppt') ? TRUE : FALSE;
//    if (empty($_FILES['files']['name'][$file_field])) {
//      // Only need to validate if the field actually has a file.
//      $form_state->setError(
//        $form['opigno_scorm_package'],
//        $this->t("The file was not uploaded.")
//      );
//    }
//
//    // Prepare folder.
//    $temporary_file_path = !$is_ppt ? 'public://external_packages' : 'public://' . ExternalPackageController::getPptConversionDir();
//    \Drupal::service('file_system')->prepareDirectory($temporary_file_path, FileSystemInterface::MODIFY_PERMISSIONS | FileSystemInterface::CREATE_DIRECTORY);
//
//    // Prepare file validators.
//    $extensions = !$is_ppt ? ['h5p zip'] : ['ppt pptx'];
//    $validators = [
//      'file_validate_extensions' => $extensions,
//    ];
//    $file = file_save_upload($file_field, $validators, $temporary_file_path);
//
//    if (!$file[0]) {
//      return $form_state->setRebuild();
//    };

    // Validate Scorm and Tincan packages.
//    $this->validateZipPackage($form, $form_state, $file[0]);

    // Set file id in form state for loading on submit.
//    $form_state->set('opigno_scorm_package', $file[0]->id());
  }

  private function validateZipPackage($form, FormStateInterface $form_state, File $file) {
    /* @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
    $file_extension = substr(strrchr($file->getFilename(), '.'), 1);
    if ($file_extension == 'zip') {
      $scorm_controller->unzipPackage($file);
      $extract_dir = 'public://opigno_scorm_extracted/scorm_' . $file->id();
      // This is a standard: these files must always be here.
      $scorm_file = $extract_dir . '/imsmanifest.xml';
      $tincan_file = $extract_dir . '/tincan.xml';
      if (!file_exists($scorm_file) && !file_exists($tincan_file)) {
        $validation = FALSE;

        $files = scandir($extract_dir);
        $count_files = count($files);

        if ($count_files == 3 && is_dir($extract_dir. '/' . $files[2])) {
          $subfolder_files = scandir($extract_dir. '/' . $files[2]);

          if (in_array('imsmanifest.xml', $subfolder_files)) {
            $source = $extract_dir. '/' . $files[2];

            $i = new \RecursiveDirectoryIterator($source);
            foreach($i as $f) {
              if($f->isFile()) {
                rename($f->getPathname(), $extract_dir . '/' . $f->getFilename());
              } else if($f->isDir()) {
                rename($f->getPathname(), $extract_dir . '/' . $f->getFilename());
                unlink($f->getPathname());
              }
            }
            $validation = TRUE;
          }
        }

        if ($validation == FALSE) {
          $form_state->setError(
            $form['opigno_scorm_package'],
            $this->t('Your file is not recognized as a valid SCORM, TinCan, or H5P package.')
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
    // Need to get file details i.e upload file name, size etc.

    $file = $form_state->getValue('opigno_scorm_package');
    $file =File::load($file[0]);
    $result = $scorm_controller->scormExtract($file);
      \Drupal::messenger()->addStatus("File successfully uploaded.");
  }
}
