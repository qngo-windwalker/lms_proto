<?php

namespace Drupal\wind_scorm\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Drupal\opigno_module\Entity\OpignoActivityInterface;
use Drupal\opigno_module\Entity\OpignoModuleInterface;
use Drupal\opigno_module\Entity\UserModuleStatus;
use Drupal\opigno_module\Entity\UserModuleStatusInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WindScormTakeCourseController
 * @see Drupal\opigno_scorm\OpignoScormPlayer
 * @package Drupal\wind_scorm\Controller
 */
class WindScormTakeCourseController extends ControllerBase {

  public function takeCourse(Request $request) {
    $file = $this->getSCORMFile();
    if (!$file) {
      return;
    }

    /** @var \Drupal\opigno_scorm\OpignoScorm $scorm_service */
    $scorm_service = \Drupal::service('opigno_scorm.scorm');
    $scorm = $scorm_service->scormLoadByFileEntity($file);

    /** @var \Drupal\opigno_scorm\OpignoScormPlayer $scorm_player */
    $scorm_player = \Drupal::service('opigno_scorm.scorm_player');
    return $scorm_player->toRendarableArray($scorm);
  }

  /**
   * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $opigno_module
   * @param \Drupal\opigno_module\Entity\OpignoActivityInterface $opigno_activity
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @see \Drupal\opigno_module\Controller\OpignoModuleController::moduleQuestionAnswerForm
   */
  public function courseActivityForm(OpignoModuleInterface $opigno_module, OpignoActivityInterface $opigno_activity) {
    $build = [];
    $user = $this->currentUser();
    //    $opigno_modules = \Drupal::entityTypeManager()->getStorage('opigno_module')->loadMultiple();
    //    foreach ($opigno_modules as $opigno_module) {
    //    }
    //    $activity_types = \Drupal::entityTypeManager()->getStorage('opigno_activity')->loadMultiple();
    //    foreach ($activity_types as $opigno_activity) {
    //    }
    $attempt = $opigno_module->getModuleActiveAttempt($user);

    if (!is_null($attempt)) {
      /** @var \Drupal\opigno_module\Entity\OpignoAnswer $existing_answer */
      $existing_answer = $opigno_activity->getUserAnswer($opigno_module, $attempt, $user);
      if (!is_null($existing_answer)) {
        $answer = $existing_answer;
      }
    }
    if (!isset($answer)) {
      /** @var \Drupal\opigno_module\Entity\OpignoAnswer $answer */
      $answer = static::entityTypeManager()->getStorage('opigno_answer')->create(array(
        'type' => $opigno_activity->getType(),
        'activity' => $opigno_activity->id(),
        'module' => $opigno_module->id(),
      ));
    }
    // Output rendered activity of the specified type.
    $build[] = \Drupal::entityTypeManager()->getViewBuilder('opigno_activity')->view($opigno_activity, 'activity');
    // Output answer form of the same activity type.
    //    $build[] = $this->entityFormBuilder()->getForm($answer);
    $build[0]['#attributes']['class'][] = 'wind-opigno-iframe';
    $build[0]['#attached']['library'][] = 'wind/scorm_player';
    return $build;
  }

  private function getSCORMFile() {
    // Loads all the files.
    $files = \Drupal\file\Entity\File::loadMultiple();
    foreach ($files as $file) {
      if($file->get('filemime')->getString() == 'application/zip'){
        return $file;
      };
    }
    return FALSE;
  }
}
