<?php

namespace Drupal\opigno_module\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opigno_module\Entity\OpignoActivity;

/**
 * Form controller for Answer edit forms.
 *
 * @ingroup opigno_module
 */
class OpignoAnswerForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\opigno_module\Entity\OpignoAnswer */
    $form = parent::buildForm($form, $form_state);
    // Hide revision_log_message field.
    unset($form['revision_log_message']);
    $entity = $this->entity;
    $activity = $entity->getActivity();
    /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
    $module = $entity->getModule();
    $form['activity'] = array(
      '#type' => 'label',
      '#title' => $activity->value,
    );
    $form['module'] = array(
      '#type' => 'label',
      '#title' => $module->value,
    );
    // Backwards navigation.
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#submit' => [
        '::backwardsNavigation',
      ],
    ];
    // Check for enabled option.
    // Also check that user already has at least 1 answered activity.
    // Check that user is not on the first activity in the module.
    $attempt = $module->getModuleActiveAttempt($this->currentUser());
    if ($attempt !== NULL) {
      $last_activity_id = $attempt->getLastActivityId();

      $activities = $module->getModuleActivities();
      $first_activity = reset($activities);
      $first_activity = $first_activity !== FALSE
        ? OpignoActivity::load($first_activity->id)
        : NULL;
      $current_activity = \Drupal::routeMatch()->getParameter('opigno_activity');

      $has_first_activity = $first_activity !== NULL;
      $has_current_activity = $current_activity !== NULL;

      $is_on_first_activity = $has_first_activity
        && $has_current_activity
        && $first_activity->id() === $current_activity->id();

      if (!$module->getBackwardsNavigation()
        || is_null($last_activity_id)
        || $is_on_first_activity) {
        $form['actions']['back']['#attributes']['disabled'] = TRUE;
      }
    }
    /* @var $answer_service \Drupal\opigno_module\ActivityAnswerManager */
    $answer_service = \Drupal::service('plugin.manager.activity_answer');
    $answer_activity_type = $activity->getType();
    if ($answer_service->hasDefinition($answer_activity_type)) {
      $answer_instance = $answer_service->createInstance($answer_activity_type);
      $answer_instance->answeringForm($form);
    }
    // Remove 'delete' button.
    unset($form['actions']['delete']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;
    $activity = $entity->getActivity();
    $module = $entity->getModule();
    $attempt = $module->getModuleActiveAttempt($this->currentUser());
    if ($attempt !== NULL) {
      $attempt->setLastActivity($activity);
      $entity->setUserModuleStatus($attempt);
      // Check if answer should be evaluated or not.
      // Make it possible to modify answer object before save.
      /* @var $answer_service \Drupal\opigno_module\ActivityAnswerManager */
      $answer_service = \Drupal::service('plugin.manager.activity_answer');
      $answer_activity_type = $activity->getType();
      if ($answer_service->hasDefinition($answer_activity_type)) {
        $answer_instance = $answer_service->createInstance($answer_activity_type);
        // Evaluation status.
        $evaluated_status = $answer_instance->evaluatedOnSave($activity) ? 1 : 0;
        // Answer score.
        $score = $answer_instance->getScore($entity);
        $entity->setScore($score);
      }
      // Set evaluation status.
      $entity->setEvaluated($evaluated_status);
      //    $entity->save();
      $attempt->save();
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Answer.', [
          '%label' => $entity->id(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Answer.', [
          '%label' => $entity->id(),
        ]));
    }
    // Query param is used to detect if we go to take page from submitted answer.
    $form_state->setRedirect('opigno_module.take_module', [
      'opigno_module' => $module->id(),
    ], ['query' => ['continue' => TRUE]]);
  }

  /**
   * {@inheritdoc}
   */
  public function backwardsNavigation(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;
    $module = $entity->getModule();
    $activity = $entity->getActivity();
    $attempt = $module->getModuleActiveAttempt($this->currentUser());
    $attempt->setLastActivity($activity);
    $attempt->save();
    // Query param is used to detect if we used backwards navigation button.
    $form_state->setRedirect('opigno_module.take_module', [
      'opigno_module' => $module->id(),
    ], ['query' => ['backwards' => TRUE]]);
  }

}
