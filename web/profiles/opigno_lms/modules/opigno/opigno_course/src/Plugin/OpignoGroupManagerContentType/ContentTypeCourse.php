<?php

namespace Drupal\opigno_course\Plugin\OpignoGroupManagerContentType;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\ContentTypeBase;
use Drupal\opigno_group_manager\OpignoGroupContent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentTypeCourse
 * @package Drupal\opigno_course\Plugin\OpignoGroupManagerContentType
 *
 * @OpignoGroupManagerContentType (
 *   id = "ContentTypeCourse",
 *   entity_type = "group",
 *   readable_name = "Course",
 *   description = "A course is a group of learning path contents",
 *   allowed_group_types = {
 *     "learning_path",
 *   },
 *   group_content_plugin_id = "subgroup:opigno_course"
 * )
 */
class ContentTypeCourse extends ContentTypeBase
{

  /**
   * Get the URL object of the main view page of a specific entity.
   *
   * @param $entity_id int The entity ID.
   *
   * @return \Drupal\Core\Url The tool entity URL.
   */
  public function getViewContentUrl($entity_id)
  {
    return Url::fromRoute('entity.group.canonical', ['group' => $entity_id]);
  }

  /**
   * Get the score of the user for a specific entity.
   *
   * @param $user_id int The user ID.
   * @param $entity_id int The entity ID.
   *
   * @return float|FALSE The score between 0 and 1. FALSE if no score found.
   */
  public function getUserScore($user_id, $entity_id)
  {
    // TODO: Implement getUserScore() method.
    return 1;
  }

  /**
   * Get the entity as a LearningPathContent.
   *
   * @param $entity_id int The entity ID.
   *
   * @return OpignoGroupContent|FALSE The content loaded in a LearningPathContent. FALSE if not possible to load.
   */
  public function getContent($entity_id)
  {
    if (!is_numeric($entity_id)) {
      return FALSE;
    }

    /** @var Group $group */
    $group = Group::load($entity_id);
    if ($group->getGroupType()->id() != 'opigno_course') {
      return FALSE;
    }


    $image_field = $group->get('field_course_image');
    if (empty($image_field->getValue())) {
      // If no image set, put default image.
      $img_url = $this->getDefaultCourseImageUrl();
      $img_alt = 'Default course image';
    }
    else {
      // If there is an image set, get the URL of it.
      $img_params = $image_field->getValue();
      $file = File::load($img_params[0]['target_id']);
      $uri = $file->getFileUri();

      // I use this function `file_create_url` because all the others
      //   methods ($file->toUrl(), URL::fromUri($uri)->toString(), ...) doesn't work...
      $url = file_create_url($uri);

      $img_url = $url;
      $img_alt = $img_params[0]['alt'];
    }

    return new OpignoGroupContent(
      $this->getPluginId(),
      $this->getEntityType(),
      $entity_id,
      $group->label(),
      $img_url,
      $img_alt
    );
  }

  /**
   * Get all the published entities in an array of LearningPathContent.
   *
   * @return OpignoGroupContent[]|FALSE The published contents or FALSE in case of error.
   */
  public function getAvailableContents()
  {
    try {
      /** @var Group[] $groups */
      $groups = \Drupal::entityTypeManager()->getStorage('group')->loadByProperties(['type' => 'opigno_course']);
    } catch (InvalidPluginDefinitionException $e) {
      // TODO: Log the error
      return FALSE;
    }

    $contents = [];
    foreach($groups as $group) {
      // Load the image.
      $image_field = $group->get('field_course_image');
      if (empty($image_field->getValue())) {
        // If no image set, put default image.
        $img_url = $this->getDefaultCourseImageUrl();
        $img_alt = 'Default course image';
      }
      else {
        // If there is an image set, get the URL of it.
        $img_params = $image_field->getValue();
        $file = File::load($img_params[0]['target_id']);
        $uri = $file->getFileUri();

        // I use this function `file_create_url` because all the others
        //   methods ($file->toUrl(), URL::fromUri($uri)->toString(), ...) doesn't work...
        $url = file_create_url($uri);

        $img_url = $url;
        $img_alt = $img_params[0]['alt'];
      }

      $contents[] = new OpignoGroupContent(
        $this->getPluginId(),
        $this->getEntityType(),
        $group->id(),
        $group->label(),
        $img_url,
        $img_alt
      );
    }
    return $contents;
  }

  /**
   * Get all the entities in an array of LearningPathContent.
   *
   * @return OpignoGroupContent[]|FALSE The contents or FALSE in case of error.
   */
  public function getAllContents()
  {
    try {
      /** @var Group[] $groups */
      $groups = \Drupal::entityTypeManager()->getStorage('group')->loadByProperties(['type' => 'opigno_course']);
    } catch (InvalidPluginDefinitionException $e) {
      // TODO: Log the error
      return FALSE;
    }

    $contents = [];
    foreach($groups as $group) {
      // Load the image.
      $image_field = $group->get('field_course_image');
      if (empty($image_field->getValue())) {
        // If no image set, put default image.
        $img_url = $this->getDefaultCourseImageUrl();
        $img_alt = 'Default course image';
      }
      else {
        // If there is an image set, get the URL of it.
        $img_params = $image_field->getValue();
        $file = File::load($img_params[0]['target_id']);
        $uri = $file->getFileUri();

        // I use this function `file_create_url` because all the others
        //   methods ($file->toUrl(), URL::fromUri($uri)->toString(), ...) doesn't work...
        $url = file_create_url($uri);

        $img_url = $url;
        $img_alt = $img_params[0]['alt'];
      }

      $contents[] = new OpignoGroupContent(
        $this->getPluginId(),
        $this->getEntityType(),
        $group->id(),
        $group->label(),
        $img_url,
        $img_alt
      );
    }
    return $contents;
  }

  /**
   * Try to get the content from a Request object.
   *
   * @param Request $request The request object
   * @return OpignoGroupContent|FALSE The content if possible. FALSE otherwise.
   */
  public function getContentFromRequest(Request $request)
  {
    $group = $request->get('group', FALSE);
    if ($group === FALSE) {
      return FALSE;
    }

    // If the value is the group ID, load the group.
    if (is_numeric($group)) {
      /** @var Group $group */
      $group = Group::load($group);
    }

    if ($group->getGroupType()->id() != 'opigno_course') {
      return FALSE;
    }

    // Load the image.
    $image_field = $group->get('field_course_image');
    if (empty($image_field->getValue())) {
      // If no image set, put default image.
      $img_url = $this->getDefaultCourseImageUrl();
      $img_alt = 'Default course image';
    }
    else {
      // If there is an image set, get the URL of it.
      $img_params = $image_field->getValue();
      $file = File::load($img_params[0]['target_id']);
      $uri = $file->getFileUri();

      // I use this function `file_create_url` because all the others
      //   methods ($file->toUrl(), URL::fromUri($uri)->toString(), ...) doesn't work...
      $url = file_create_url($uri);

      $img_url = $url;
      $img_alt = $img_params[0]['alt'];
    }

    return new OpignoGroupContent(
      $this->getPluginId(),
      $this,
      $group->id(),
      $group->label(),
      $img_url,
      $img_alt
    );
  }

  /**
   * Get the form object based on the entity ID.
   * If no entity given in parameter, return the entity creation form object.
   *
   * @param $entity_id int The entity ID.
   * @return EntityFormInterface
   */
  public function getFormObject($entity_id = NULL)
  {
    if (empty($entity_id)) {
      $form = \Drupal::entityTypeManager()->getFormObject($this->getEntityType(), 'add');
      $entity = Group::create(['type' => 'opigno_course']);
    }
    else {
      $form = \Drupal::entityTypeManager()->getFormObject($this->getEntityType(), 'edit');
      $entity = Group::load($entity_id);
    }
    $form->setEntity($entity);

    return $form;
  }

  /**
   * Return TRUE if the page should show the "next" action button, even if the score does not permit the user to go next.
   *
   * Returning TRUE will not automatically show the button. The button will show up only if this method returns
   *   TRUE and if there is a next step available and if the user is able to go to this next content.
   *
   * @return bool
   */
  public function shouldShowNext()
  {
    // For now, show the next button only if the page is a "view" page from a group of type "opigno_course"
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name != 'entity.group.canonical') {
      return FALSE;
    }

    $group = \Drupal::request()->get('group');
    if (empty($group) || $group->getGroupType()->id() != 'opigno_course') {
      return FALSE;
    }

    return TRUE;
  }

  public function getDefaultCourseImageUrl()
  {
    $base_url = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
    $path = \Drupal::service('module_handler')->getModule('opigno_course')->getPath();
    return $base_url . $path . '/img/img_course.svg';
  }

}
