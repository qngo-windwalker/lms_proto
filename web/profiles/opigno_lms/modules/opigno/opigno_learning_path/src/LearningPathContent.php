<?php

namespace Drupal\opigno_learning_path;

use Drupal\opigno_learning_path\Entity\LPManagedContent;

class LearningPathContent
{
  private $learningPathContentTypeId;
  private $entityType;
  private $entityId;
  private $title;
  private $imageUrl;
  private $imageAlt;

  public function __construct($learning_path_content_type_id, $entity_type, $entity_id, $title, $image_url, $image_alt) {
    $this->setLearningPathContentTypeId($learning_path_content_type_id);
    $this->setEntityType($entity_type);
    $this->setEntityId($entity_id);
    $this->setTitle($title);
    $this->setImageUrl($image_url);
    $this->setImageAlt($image_alt);
  }

  function __toString() {
    return implode('.', [
      self::class,
      $this->entityType,
      $this->entityId
    ]);
  }

  /**
   * @param LPManagedContent|NULL $content
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function toManagerArray(LPManagedContent $content = NULL) {
    if ($content === NULL) {
      $cid = '';
      $is_mandatory = FALSE;
      $success_score_min = 0;
      $parents_links = [];
    }
    else {
      $cid = $content->id();
      $is_mandatory = $content->isMandatory();
      $success_score_min = $content->getSuccessScoreMin();
      $parents_links = $content->getParentsLinks();
    }

    $this_array = [
      'cid' => $cid,
      'entityId' => $this->getEntityId(),
      'entityType' => $this->getLearningPathContentTypeId(), // TODO: Remove this. Avoid duplicate.
      'entityBundle' => $this->getLearningPathContentTypeId(), // TODO: Remove this. Avoid duplicate.
      'contentType' => $this->getLearningPathContentTypeId(),
      'title' => $this->getTitle(),
      'imageUrl' => $this->getImageUrl(),
      'imageAlt' => $this->getImageAlt(),
      'isMandatory' => $is_mandatory,
      'successScoreMin' => $success_score_min
    ];

    $parents = [];
    foreach ($parents_links as $link) {
      if (get_class($link) != 'Drupal\opigno_learning_path\Entity\LPManagedLink') {
        continue;
      }

      $parents[] = ['cid' => $link->getParentContentId(), 'minScore' => $link->getRequiredScore()];
    }
    $this_array['parents'] = $parents;

    return $this_array;
  }

  /**
   * @return string
   */
  public function getEntityType() {
    return $this->entityType;
  }

  /**
   * @param string $entity_type
   */
  public function setEntityType($entity_type) {
    $this->entityType = $entity_type;
  }

  /**
   * @return string
   */
  public function getEntityId() {
    return $this->entityId;
  }

  /**
   * @param string $entity_id
   */
  public function setEntityId($entity_id) {
    $this->entityId = $entity_id;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param string $entity_title
   */
  public function setTitle($entity_title) {
    $this->title = $entity_title;
  }

  /**
   * @return mixed
   */
  public function getLearningPathContentTypeId()
  {
    return $this->learningPathContentTypeId;
  }

  /**
   * @param mixed $learning_path_content_type_id
   */
  public function setLearningPathContentTypeId($learning_path_content_type_id)
  {
    $this->learningPathContentTypeId = $learning_path_content_type_id;
  }

  /**
   * @return mixed
   */
  public function getImageUrl()
  {
    return $this->imageUrl;
  }

  /**
   * @param mixed $imageUrl
   */
  public function setImageUrl($image_url)
  {
    $this->imageUrl = $image_url;
  }

  /**
   * @return mixed
   */
  public function getImageAlt()
  {
    return $this->imageAlt;
  }

  /**
   * @param mixed $imageAlt
   */
  public function setImageAlt($image_alt)
  {
    $this->imageAlt = $image_alt;
  }
}
