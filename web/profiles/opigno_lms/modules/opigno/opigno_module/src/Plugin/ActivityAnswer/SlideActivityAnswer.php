<?php

namespace Drupal\opigno_module\Plugin\ActivityAnswer;

use Drupal\opigno_module\ActivityAnswerPluginBase;
use Drupal\opigno_module\Entity\OpignoActivityInterface;
use Drupal\opigno_module\Entity\OpignoAnswerInterface;

/**
 * @ActivityAnswer(
 *   id="opigno_slide",
 * )
 */
class SlideActivityAnswer extends ActivityAnswerPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluatedOnSave(OpignoActivityInterface $activity) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getScore(OpignoAnswerInterface $answer) {
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $score = 0;
    $activity = $answer->getActivity();
    $score_query = $db_connection->select('opigno_module_relationship', 'omr')
      ->fields('omr', ['max_score'])
      ->condition('omr.parent_id', $answer->getModule()->id())
      ->condition('omr.parent_vid', $answer->getModule()->getRevisionId())
      ->condition('omr.child_id', $activity->id())
      ->condition('omr.child_vid', $activity->getRevisionId());
    $score_result = $score_query->execute()->fetchObject();
    if ($score_result) {
      $score = $score_result->max_score;
    }
    return $score;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswerResultItemHeaders() {
    return [
      $this->t('Slide content'),
    ];
  }

  public function getAnswerResultItemData(OpignoAnswerInterface $answer) {
    $data = [];
    $slide_activity = $answer->getActivity();
    $slide = $slide_activity->get('opigno_slide_pdf')->entity;

    if ($slide !== NULL) {
      $file_link = [
        '#theme' => 'file_link',
        '#file' => $slide,
      ];
      $data['item'][] = \Drupal::service('renderer')->render($file_link);
    }

    return $data;
  }

}
