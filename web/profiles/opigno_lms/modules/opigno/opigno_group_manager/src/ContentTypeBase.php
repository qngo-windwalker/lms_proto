<?php

namespace Drupal\opigno_group_manager;

use Drupal\Core\Plugin\PluginBase;

/**
 * Class ContentTypeBase
 * @package Drupal\opigno_group_manager
 *
 * This class contains the basics that every plugin implementation of Learning Path Content Type should extend from.
 */
abstract class ContentTypeBase extends PluginBase implements ContentTypeInterface
{

  /**
   * @return string
   */
  public function getEntityType()
  {
    return $this->pluginDefinition['entity_type'];
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->pluginDefinition['id'];
  }

  /**
   * @return string
   */
  public function getReadableName()
  {
    return $this->pluginDefinition['readable_name'];
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->pluginDefinition['description'];
  }

  /**
   * @return array
   */
  public function getAllowedGroupTypes()
  {
    return $this->pluginDefinition['allowed_group_types'];
  }

  /**
   * @return string
   */
  public function getGroupContentPluginId()
  {
    return $this->pluginDefinition['group_content_plugin_id'];
  }

  /**
   * Get the URL object for starting the quiz.
   *
   * @param $content_id int The content ID (ex: node ID).
   *
   * @return \Drupal\Core\Url The URL to use to start the "test" for a student.
   */
  public function getStartContentUrl($content_id) {
    return $this->getViewContentUrl($content_id);
  }

  /**
   * Answer if the current page should show the "finish" button.
   * By default, it returns the value from shouldShowNext().
   *
   * @return bool TRUE if the page should show the "finish" button. FALSE otherwise.
   */
  public function shouldShowFinish()
  {
    return $this->shouldShowNext();
  }

}
