<?php

namespace Drupal\opigno_learning_path;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContentTypeInterface extends PluginInspectionInterface
{

  /**
   * Get the URL object of the main view page of a specific entity.
   *
   * @param $entity_id int The entity ID.
   *
   * @return \Drupal\Core\Url The tool entity URL.
   */
  public function getViewContentUrl($entity_id);

  /**
   * Get the score of the user for a specific entity.
   *
   * @param $user_id int The user ID.
   * @param $entity_id int The entity ID.
   *
   * @return float|FALSE The score between 0 and 1. FALSE if no score found.
   */
  public function getUserScore($user_id, $entity_id);

  /**
   * Get the entity as a LearningPathContent.
   *
   * @param $entity_id int The entity ID.
   *
   * @return LearningPathContent|FALSE The content loaded in a LearningPathContent. FALSE if not possible to load.
   */
  public function getContent($entity_id);

  /**
   * Get all the published entities in an array of LearningPathContent.
   *
   * @return LearningPathContent[]|FALSE The published contents or FALSE in case of error.
   */
  public function getAvailableContents();

  /**
   * Get all the entities in an array of LearningPathContent.
   *
   * @return LearningPathContent[]|FALSE The contents or FALSE in case of error.
   */
  public function getAllContents();

  /**
   * Try to get the content from a Request object.
   *
   * @param Request $request The request object
   * @return LearningPathContent|FALSE The content if possible. FALSE otherwise.
   */
  public function getContentFromRequest(Request $request);

  /**
   * Get the form object based on the entity ID.
   * If no entity given in parameter, return the entity creation form object.
   *
   * @param $entity_id int The entity ID.
   * @return EntityFormInterface
   */
  public function getFormObject($entity_id = NULL);

  /**
   * Return TRUE if the page should show the "next" action button, even if the score does not permit the user to go next.
   *
   * Returning TRUE will not automatically show the button. The button will show up only if this method returns
   *   TRUE and if there is a next step available and if the user is able to go to this next content.
   *
   * @return bool
   */
  public function shouldShowNext();
}
