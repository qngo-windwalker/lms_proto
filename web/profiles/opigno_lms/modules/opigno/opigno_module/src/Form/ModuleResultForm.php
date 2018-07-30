<?php

namespace Drupal\opigno_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_module\Entity\OpignoModuleInterface;
use Drupal\opigno_module\Entity\UserModuleStatus;

/**
 * Class ModuleResultForm.
 *
 * @package Drupal\opigno_module\Form
 */
class ModuleResultForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opigno_module_result_form';
  }

  /**
   * Title callback for the form.
   */
  public function formTitle(OpignoModule $opigno_module = NULL) {
    return $this->t('Edit module result for %module_name', ['%module_name' => $opigno_module->getName()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, OpignoModule $opigno_module = NULL, UserModuleStatus $user_module_status = NULL) {
    // Get attempt answers.
    $form['answers'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
    ];
    $answers = $user_module_status->getAnswers();
    $module_activities = $opigno_module->getModuleActivities();
    foreach ($answers as $answer_id => $answer) {
      $answer_activity = $answer->getActivity();
      $form['answers'][$answer_id] = [
        '#type' => 'fieldset',
        '#title' => Link::createFromRoute($this->t('Activity: %activity', ['%activity' => $answer_activity->getName()]), 'entity.opigno_activity.canonical', ['opigno_activity' => $answer_activity->id()])->toString(),
      ];
      $form['answers'][$answer_id]['answer_markup'] = \Drupal::entityTypeManager()->getViewBuilder('opigno_answer')->view($answer);
      if (!$answer->isEvaluated()) {
        $form['answers'][$answer_id]['score'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Score'),
          '#required' => TRUE,
        ];
      }
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save score'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $user_status = $build_info['args'][1];
    $answer_storage = \Drupal::entityTypeManager()->getStorage('opigno_answer');
    $form_values = $form_state->getValues();
    foreach ($form_values['answers'] as $answer_id => $value) {
      if (isset($value['score'])) {
        $answer = $answer_storage->load($answer_id);
        $answer->setScore($value['score']);
        $answer->setEvaluated(1);
        $answer->save();
      }
    }
    $score = $user_status->calculateScore();
    $max_score = $user_status->calculateMaxScore();
    if ($max_score > 0) {
      $percents = round(($score / $max_score) * 100);
    }
    else {
      $percents = 100;
    }
    $user_status->setScore((int) $percents);
    $user_status->setMaxScore($max_score);
    $user_status->setEvaluated(1);
    $user_status->save();

  }

}
