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
    $start_sco = $this->getTestStartSCO();
    $sco_identifiers = array('item_1' => '1');
    $data = $this->getTestCMIData();
    $paths = $this->getTestCMIPaths();
    $scos_suspend_data = array();
    $scorm_version = '2004';
    // @see Drupal\opigno_scorm\OpignoScormPlayer
    return [
//      '#theme' => 'wind_scorm__player',
      '#theme' => 'opigno_scorm__player',
//      '#scorm_id' => $scorm->id,
      '#scorm_id' => 1,
      '#tree' =>  NULL ,
      '#start_sco' => $start_sco,
      '#attached' => [
        'library' => ['opigno_scorm/opigno-scorm-player', 'wind_scorm/scorm_player'],
        'drupalSettings' => [
          'opignoScormUIPlayer' => [
            'cmiPaths' => $paths,
            'cmiData' => $data,
            'scoIdentifiers' => $sco_identifiers,
            'cmiSuspendItems' => $scos_suspend_data,
          ],
          'scormVersion' => $scorm_version,
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

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

  private function getTestStartSCO() {
    $start_sco = new \stdClass();
    // Add base path for player link.
    global $base_path;
    $start_sco->id = 1;
    $start_sco->base_path = $base_path;
    return $start_sco;
  }

  private function getTestCMIData(){
    return array (
      'cmi.score.raw' => '',
      'cmi.score.min' => '',
      'cmi.score.max' => '',
      'cmi.score.scaled' => '',
      'cmi.success_status' => '',
      'cmi.objectives' =>
        array (
          0 =>
            array (
              'id' => 'PRIMARYOBJ',
              'score' =>
                array (
                  'scaled' => 0,
                  'raw' => 0,
                  'min' => 0,
                  'max' => 0,
                ),
              'success_status' => '',
              'completion_status' => '',
              'progress_measure' => '',
              'description' => '',
            ),
          1 =>
            array (
              'id' => 'm01',
              'score' =>
                array (
                  'scaled' => 0,
                  'raw' => 0,
                  'min' => 0,
                  'max' => 0,
                ),
              'success_status' => '',
              'completion_status' => '',
              'progress_measure' => '',
              'description' => '',
            ),
        ),
      'cmi.suspend_data' => '',
      'cmi.location' => '',
      'cmi.completion_status' => 'unknown',
      'cmi.exit' => '',
      'cmi.learner_id' => '1',
      'cmi.learner_name' => 'admin',
      'cmi.learner_preference._children' => 'audio_level,language,delivery_speed,audio_captioning',
      'cmi.learner_preference.audio_level' => 1,
      'cmi.learner_preference.language' => '',
      'cmi.learner_preference.delivery_speed' => 1,
      'cmi.learner_preference.audio_captioning' => 0,
    );
  }

  private function getTestCMIPaths(){
    return array (
      'cmi.score.raw' =>
        array (
        ),
      'cmi.score.min' =>
        array (
        ),
      'cmi.score.max' =>
        array (
        ),
      'cmi.score.scaled' =>
        array (
        ),
      'cmi.suspend_data' =>
        array (
        ),
      'cmi.success_status' =>
        array (
        ),
      'cmi.objectives' =>
        array (
        ),
      'cmi.objectives._count' =>
        array (
          'readOnly' => 1,
        ),
      'cmi.objectives._children' =>
        array (
          'readOnly' => 1,
        ),
      'cmi.objectives.n.id' =>
        array (
        ),
      'cmi.objectives.n.score' =>
        array (
        ),
      'cmi.objectives.n.score._children' =>
        array (
          'readOnly' => 1,
        ),
      'cmi.objectives.n.score.scaled' =>
        array (
        ),
      'cmi.objectives.n.score.raw' =>
        array (
        ),
      'cmi.objectives.n.score.min' =>
        array (
        ),
      'cmi.objectives.n.score.max' =>
        array (
        ),
      'cmi.objectives.n.success_status' =>
        array (
        ),
      'cmi.objectives.n.completion_status' =>
        array (
        ),
      'cmi.objectives.n.progress_measure' =>
        array (
        ),
      'cmi.objectives.n.description' =>
        array (
        ),
      'cmi.location' =>
        array (
        ),
      'cmi.completion_status' =>
        array (
        ),
      'cmi.exit' =>
        array (
        ),
      'cmi.learner_id' =>
        array (
        ),
      'cmi.learner_name' =>
        array (
        ),
      'cmi.learner_preference._children' =>
        array (
        ),
      'cmi.learner_preference.audio_level' =>
        array (
        ),
      'cmi.learner_preference.language' =>
        array (
        ),
      'cmi.learner_preference.delivery_speed' =>
        array (
        ),
      'cmi.learner_preference.audio_captioning' =>
        array (
        ),
    );

  }
}
