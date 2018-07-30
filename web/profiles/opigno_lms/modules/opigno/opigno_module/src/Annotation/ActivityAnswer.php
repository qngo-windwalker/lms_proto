<?php

namespace Drupal\opigno_module\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for ActivityType plugin.
 *
 * @Annotation
 */
class ActivityAnswer extends Plugin {

  /**
   * Plugin id.
   */
  public $id;

  /**
   * Plugin field activity type bundle.
   */
  public $activityTypeBundle;

}
