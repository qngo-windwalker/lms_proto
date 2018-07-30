<?php

namespace Drupal\opigno_learning_path\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\opigno_learning_path\LatestActivityInterface;

/**
 * Defines the Latest Activity entity.
 *
 * @ingroup opigno_learning_path
 *
 * @ContentEntityType(
 *   id = "opigno_latest_group_activity",
 *   label = @Translation("Latest Activity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "opigno_latest_group_activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 * )
 */
class LatestActivity extends ContentEntityBase implements LatestActivityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Latest Activity entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Latest Activity entity.'))
      ->setReadOnly(TRUE);

    $fields['training'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Training'))
      ->setDescription(t('The Training of the Latest Activity entity.'))
      ->setSettings([
        'target_type' => 'group',
        'default_value' => 0,
      ]);

    $fields['module'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Module'))
      ->setDescription(t('The Module of the Latest Activity entity.'))
      ->setSettings([
        'target_type' => 'opigno_module',
        'default_value' => 0,
      ]);

    $fields['timestamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Timestamp'))
      ->setDescription(t('The Timestamp of the Latest Activity entity.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getTraining() {
    $value = $this->get('training')->getValue();

    if (!isset($value)) {
      return NULL;
    }

    return $value[0]['target_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function setTraining($id) {
    $this->set('training', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getModule() {
    $value = $this->get('module')->getValue();

    if (!isset($value)) {
      return NULL;
    }

    return $value[0]['target_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function setModule($id) {
    $this->set('module', $id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp() {
    $value = $this->get('timestamp')->getValue();

    if (!isset($value)) {
      return NULL;
    }

    return $value[0]['value'];
  }

  /**
   * {@inheritdoc}
   */
  public function setTimestamp($value) {
    $this->set('timestamp', $value);
    return $this;
  }

  /**
   * Creates or updates latest group activity.
   *
   * @param int $training_id
   *   The training ID.
   * @param int $module_id
   *   The module ID.
   *
   * @return \Drupal\opigno_learning_path\LatestActivityInterface
   *   Created or updated entity.
   */
  public static function insertGroupActivity($training_id, $module_id) {
    $query = \Drupal::entityQuery('opigno_latest_group_activity');
    $ids = $query
      ->condition('training', $training_id)
      ->condition('module', $module_id)
      ->sort('timestamp', 'DESC')
      ->range(0, 1)
      ->execute();
    $id = reset($ids);

    if ($id !== FALSE) {
      $activity = LatestActivity::load($id);
    }
    else {
      $activity = LatestActivity::create();
      $activity->setTraining($training_id);
      $activity->setModule($module_id);
    }

    $timestamp = \Drupal::time()->getRequestTime();
    $activity->setTimestamp($timestamp);
    $activity->save();

    return $activity;
  }

}
