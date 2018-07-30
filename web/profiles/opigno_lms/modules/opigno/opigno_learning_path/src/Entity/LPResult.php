<?php

namespace Drupal\opigno_learning_path\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * Defines the Learning Path Content entity.
 *
 * @ingroup opigno_learning_path
 *
 * @ContentEntityType(
 *   id = "learning_path_result",
 *   label = @Translation("Learning Path Result"),
 *   base_table = "learning_path_result",
 *   entity_keys = {
 *     "id" = "id",
 *     "learning_path_id" = "learning_path_id",
 *     "user_id" = "user_id",
 *     "has_passed" = "has_passed"
 *   }
 * )
 */
class LPResult extends ContentEntityBase {
  public static function createWithValues(
    $learning_path_id,
    $user_id,
    $has_passed,
    $finished
  ) {
    $values = [
      'learning_path_id' => $learning_path_id,
      'user_id' => $user_id,
      'has_passed' => $has_passed,
      'finished' => $finished,
    ];

    return parent::create($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  public function getLearningPathId() {
    return $this->get('learning_path_id')->target_id;
  }

  public function setLearningPathId($learning_path_id) {
    $this->set('learning_path_id', $learning_path_id);
    return $this;
  }

  public function getLearningPath() {
    return $this->get('learning_path_id')->entity;
  }

  public function setLearningPath(Group $learning_path)
  {
    // TODO: Check the group type before saving.
    $this->setLearningPathId($learning_path->id());
    return $this;
  }

  public function getUserId() {
    return $this->get('user_id')->target_id;
  }

  public function setUserId($user_id) {
    $this->set('user_id', $user_id);
    return $this;
  }

  /**
   * @return User
   */
  public function getUser() {
    return $this->get('user_id')->entity;
  }

  public function setUser(User $user) {
    $this->set('user_id', $user->id());
    return $this;
  }

  public function hasPassed() {
    return $this->get('has_passed')->value;
  }

  public function setHasPassed($has_passed) {
    $this->set('has_passed', $has_passed);
    return $this;
  }

  public static function learningPathUserProgress(Group $group, $uid) {
    $progress = 0;
    $contents = LPManagedContent::loadByLearningPathId($group->id());
    if (!empty($contents)) {
      $content_count = count($contents);
      foreach ($contents as $content) {
        $content_type = $content->getLearningPathContentType();
        $user_score = $content_type->getUserScore($uid, $content->getEntityId());
        $progress += $user_score;
      }
      $progress = round(($progress / $content_count) * 100);
    }
    return $progress;
  }

  /**
   * {@inheritdoc}
   */
  public function isStarted() {
    return (bool) $this->get('started') != 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setStarted($timestamp) {
    $this->set('started', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isFinished() {
    return (bool) $this->get('finished') != 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setFinished($timestamp) {
    $this->set('finished', $timestamp);
    return $this;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User entity object.
   *
   * @return \Drupal\Core\Entity\EntityInterface|FALSE
   */
  public static function getCurrentLPAttempt(Group $group, AccountInterface $user) {
    $results_storage = \Drupal::entityTypeManager()->getStorage('learning_path_result');
    $query = $results_storage->getQuery();
    $results = $query
      ->condition('learning_path_id', $group->id())
      ->condition('user_id', $user->id())
      ->condition('finished', 0)
      ->execute();
    return !empty($results) ? $results_storage->load(key($results)) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['learning_path_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Learning Path')
      ->setCardinality(1)
      ->setSetting('target_type', 'group')
      ->setSetting('handler_settings',
        [
          'target_bundles' => ['opigno_learning_path' => 'opigno_learning_path'],
        ]);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('User')
      ->setSetting('target_type', 'user');

    $fields['has_passed'] = BaseFieldDefinition::create('boolean')
      ->setLabel('Has passed')
      ->setDescription('Define if the user has passed the learning path')
      ->setDefaultValue(FALSE);

    $fields['started'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Started'))
      ->setDescription(t('The time that the LP attempt has started.'));

    $fields['finished'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Finished'))
      ->setDescription(t('The time that the LP attempt finished.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Creation datetime'))
      ->setDescription(t('The time that the result was saved.'));

    return $fields;
  }

  /**
   * @param array $properties
   * @return \Drupal\Core\Entity\EntityInterface[]|self[]
   * @throws InvalidPluginDefinitionException
   */
  public static function loadByProperties($properties) {
    return \Drupal::entityTypeManager()->getStorage('learning_path_result')->loadByProperties($properties);
  }

  /**
   * @param Group $learning_path
   * @return \Drupal\Core\Entity\EntityInterface[]|self[]
   * @throws InvalidPluginDefinitionException
   */
  public static function loadByLearningPath(Group $learning_path) {
    return self::loadByProperties([
      'learning_path_id' => $learning_path->id()
    ]);
  }
}
