<?php

namespace Drupal\opigno_scorm\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ScormPackage constraint.
 */
class ScormPackageConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {

    if (!$item = $items->first()) {
      return;
    }
    $activity = $item->getEntity();

    $scorm_file = $activity->get('opigno_scorm_package')->entity;
    /* @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
    $scorm_controller->unzipPackage($scorm_file);
    $extract_dir = 'public://opigno_scorm_extracted/scorm_' . $scorm_file->id();

    // This is a standard: the manifest file will always be here.
    $manifest_file = $extract_dir . '/imsmanifest.xml';
    if (!file_exists($manifest_file)) {
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
        $this->context->addViolation($constraint->missingManifestFile);
      }
    }
  }

}
