<?php

namespace Drupal\opigno_scorm\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures Scorm package.
 *
 * @Constraint(
 *   id = "ScormPackage",
 *   label = @Translation("Scorm package.", context = "Validation")
 * )
 */
class ScormPackageConstraint extends Constraint {

  public $missingManifestFile = 'Scorm package is wrong. Missing the Imsmanifest File.';

}
