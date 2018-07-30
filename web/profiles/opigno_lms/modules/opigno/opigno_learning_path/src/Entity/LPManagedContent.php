<?php

namespace Drupal\opigno_learning_path\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\group\Entity\Group;
use Drupal\opigno_learning_path\ContentTypeBase;
use Drupal\user\UserInterface;

/**
 * Defines the Learning Path Content entity.
 *
 * @ingroup opigno_learning_path
 *
 * @ContentEntityType(
 *   id = "learning_path_content",
 *   label = @Translation("Learning Path Content"),
 *   base_table = "learning_path_content",
 *   entity_keys = {
 *     "id" = "id",
 *     "learning_path_id" = "learning_path_id",
 *     "lp_content_type_id" = "lp_content_type_id",
 *     "entity_id" = "entity_id",
 *     "success_score_min" = "success_score_min",
 *     "is_mandatory" = "is_mandatory",
 *     "coordinate_x" = "coordinate_x",
 *     "coordinate_y" = "coordinate_y",
 *   }
 * )
 */
class LPManagedContent extends ContentEntityBase
{
  /**
   * Helper method to create a new LPManagedContent object with the values passed in param.
   * It's not saved automatically. You need to do $obj->save().
   *
   * @param int $learning_path_id The learning path group ID.
   * @param string $lp_content_type_id The content type plugin ID.
   * @param int $entity_id The drupal entity ID.
   * @param int $success_score_min The minimum success score to pass the learning path.
   * @param int $is_mandatory Set if the content is mandatory to pass the learning path.
   * @param int $coordinate_x The X coordinate for this content in the learning path.
   * @param int $coordinate_y The Y coordinate for this content in the learning path.
   *
   * @return \Drupal\Core\Entity\EntityInterface|self
   */
  public static function createWithValues(
    $learning_path_id,
    $lp_content_type_id,
    $entity_id,
    $success_score_min = 0,
    $is_mandatory = 0,
    $coordinate_x = 0,
    $coordinate_y = 0
  ) {
    $values = [
      'learning_path_id' => $learning_path_id,
      'lp_content_type_id' => $lp_content_type_id,
      'entity_id' => $entity_id,
      'success_score_min' => $success_score_min,
      'is_mandatory' => $is_mandatory,
      'coordinate_x' => $coordinate_x,
      'coordinate_y' => $coordinate_y,
    ];

    return parent::create($values);
  }

  /**
   * @return int The learning path group ID.
   */
  public function getLearningPathId() {
    return $this->get('learning_path_id')->target_id;
  }

  /**
   * @param int $learning_path_id The learning path ID.
   * @return $this
   */
  public function setLearningPathId($learning_path_id)
  {
    $this->set('learning_path_id', $learning_path_id);
    return $this;
  }

  /**
   * @return Group The learning path entity.
   */
  public function getLearningPath()
  {
    return $this->get('learning_path_id')->entity;
  }

  /**
   * @param Group $learning_path The learning path group entity.
   * @return $this|FALSE $this if alright. FALSE if the group is not a learning path.
   */
  public function setLearningPath(Group $learning_path)
  {
    if ($learning_path->getGroupType()->id() != 'learning_path') {
      return FALSE;
    }

    $this->setLearningPathId($learning_path->id());
    return $this;
  }

  /**
   * @return string The learning path content type plugin ID.
   */
  public function getLearningPathContentTypeId()
  {
    return $this->get('lp_content_type_id')->value;
  }

  /**
   * @param string $lp_content_type_id The learning path content type plugin ID.
   * @return $this
   */
  public function setLearningPathContentTypeId($lp_content_type_id)
  {
    $this->set('lp_content_type_id', $lp_content_type_id);
    return $this;
  }

  /**
   * @return int The drupal entity ID.
   */
  public function getEntityId()
  {
    return $this->get('entity_id')->value;
  }

  /**
   * @param int $entity_id The drupal entity ID.
   * @return $this
   */
  public function setEntityId($entity_id)
  {
    $this->set('entity_id', $entity_id);
    return $this;
  }

  /**
   * @return int The minimum score to success this learning path,
   */
  public function getSuccessScoreMin()
  {
    return $this->get('success_score_min')->value;
  }

  /**
   * @param int $success_score_min The minimum score to success this learning path,
   * @return $this
   */
  public function setSuccessScoreMin($success_score_min)
  {
    $this->set('success_score_min', $success_score_min);
    return $this;
  }

  /**
   * @return bool TRUE if this content is mandatory to success this learning path.
   *   FALSE otherwise.
   */
  public function isMandatory()
  {
    return $this->get('is_mandatory')->value;
  }

  /**
   * @param bool $is_mandatory TRUE if this content is mandatory to success this learning path.
   *   FALSE otherwise.
   * @return $this
   */
  public function setMandatory($is_mandatory)
  {
    $this->set('is_mandatory', $is_mandatory);
    return $this;
  }

  /**
   * @return int The X coordinate.
   */
  public function getCoordinateX()
  {
    return $this->get('coordinate_x')->value;
  }

  /**
   * @param int $coordinate_x The X coordinate.
   * @return $this
   */
  public function setCoordinateX($coordinate_x)
  {
    $this->set('coordinate_x', $coordinate_x);
    return $this;
  }

  /**
   * @return int The Y coordinate.
   */
  public function getCoordinateY()
  {
    return $this->get('coordinate_y')->value;
  }

  /**
   * @param int $coordinate_y The Y coordinate.
   * @return $this
   */
  public function setCoordinateY($coordinate_y)
  {
    $this->set('coordinate_y', $coordinate_y);
    return $this;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface[]|LPManagedLink[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getParentsLinks() {
    return LPManagedLink::loadByProperties([
      'learning_path_id' => $this->getLearningPathId(),
      'child_content_id' => $this->id()
    ]);
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface[]|LPManagedLink[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getChildrenLinks() {
    return LPManagedLink::loadByProperties([
      'learning_path_id' => $this->getLearningPathId(),
      'parent_content_id' => $this->id()
    ]);
  }

  /**
   * @return bool TRUE if this content has a child. FALSE otherwise.
   * @throws InvalidPluginDefinitionException
   */
  public function hasChildLink() {
    return !empty($this->getChildrenLinks());
  }

  /**
   * Get the content type object of this content.
   *
   * @return ContentTypeBase|object
   */
  public function getLearningPathContentType() {
    $manager = \Drupal::getContainer()->get('opigno_learning_path.content_types.manager');
    return $manager->createInstance($this->getLearningPathContentTypeId());
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
          'target_bundles' => ['opigno_learning_path' => 'opigno_learning_path']
        ]);

    $fields['lp_content_type_id'] = BaseFieldDefinition::create('string')
      ->setLabel('Learning Path Content Type ID')
      ->setDescription('The content type ID (should be a plugin ID)');

    $fields['entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel('Entity ID')
      ->setDescription('The entity ID');

    $fields['success_score_min'] = BaseFieldDefinition::create('integer')
      ->setLabel('Success score minimum')
      ->setDescription('The minimum score to success this content')
      ->setDefaultValue(0);

    $fields['is_mandatory'] = BaseFieldDefinition::create('boolean')
      ->setLabel('Is mandatory')
      ->setDescription('Indicate if this content is mandatory to succeed the learning path')
      ->setDefaultValue(FALSE);

    $fields['coordinate_x'] = BaseFieldDefinition::create('integer')
      ->setLabel('Coordinate X')
      ->setDescription('The X coordinate in this learning path manager')
      ->setDefaultValue(0);

    $fields['coordinate_y'] = BaseFieldDefinition::create('integer')
      ->setLabel('Coordinate Y')
      ->setDescription('The Y coordinate in this learning path manager')
      ->setDefaultValue(0);

    return $fields;
  }

  /**
   * Load one or more LPManagedContent filtered by the properties given in param.
   * The available properties are the entity_keys specified in the header of this LPManagedContent class.
   *
   * Best is to avoid to use this method and create a specific method for your search, like the
   * method loadByLearningPathId.
   * @see LPManagedContent::loadByLearningPathId()
   *
   * @param array $properties The properties to search for.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|self[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public static function loadByProperties($properties) {
    return \Drupal::entityTypeManager()->getStorage('learning_path_content')->loadByProperties($properties);
  }

  /**
   * Load the contents linked to a specific learning path.
   *
   * @param $learning_path_id int The Learning Path ID.
   * @return array|\Drupal\Core\Entity\EntityInterface[]|self[]
   */
  public static function loadByLearningPathId($learning_path_id) {
    try {
      return self::loadByProperties(['learning_path_id' => $learning_path_id]);
    } catch (InvalidPluginDefinitionException $e) {
      return [];
    }
  }

  /**
   * Deletes the content from database.
   *
   * @throws InvalidPluginDefinitionException
   */
  public function delete() {
    // First, delete all the links associated to this content.
    $as_child_links = LPManagedLink::loadByProperties(['learning_path_id' => $this->getLearningPathId(), 'child_content_id' => $this->id()]);
    $as_parent_links = LPManagedLink::loadByProperties(['learning_path_id' => $this->getLearningPathId(), 'parent_content_id' => $this->id()]);
    /** @var LPManagedLink[] $all_links */
    $all_links = array_merge($as_child_links, $as_parent_links);

    // TODO: Maybe use the entityStorage to bulk delete ?
    // Delete the links.
    foreach ($all_links as $link) {
      $link->delete();
    }

    parent::delete();
  }

  public static function getFirstStep($learning_path_id) {
    // The first step is the content who has no parents.
    // First, get all the contents.
    $contents = self::loadByLearningPathId($learning_path_id);

    // Then, check which content has no parent link.
    foreach ($contents as $content) {
      $parents = $content->getParentsLinks();
      if (empty($parents)) {
        return $content;
      }
    }

    return FALSE;
  }

  /**
   * Get the next LPManagedContent object according to the user score.
   *
   * @param int $user_score The user score for this content.
   *
   * @return bool|LPManagedContent FALSE if no next content. The next LPManagedContent if there is a next content.
   * @throws InvalidPluginDefinitionException
   */
  public function getNextStep($user_score) {
    // Get the child link that has the required_score higher than the $score param and
    //   that has the higher required_score.
    $query = \Drupal::entityTypeManager()->getStorage('learning_path_link')->getQuery();
    $query->condition('parent_content_id', $this->id());
    $query->condition('required_score', $user_score, '<=');
    $query->sort('required_score', 'DESC');
    $query->range(0, 1);
    $result = $query->execute();

    // If no result, return FALSE.
    if (empty($result)) {
      return FALSE;
    }

    // If a result is found, return the next content object.
    $next_step_id = array_pop($result);
    $next_step_link = LPManagedLink::load($next_step_id);
    return $next_step_link->getChildContent();
  }

}
