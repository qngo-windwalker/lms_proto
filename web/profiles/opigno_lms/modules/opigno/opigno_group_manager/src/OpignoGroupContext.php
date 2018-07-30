<?php

namespace Drupal\opigno_group_manager;

use Drupal\Core\Cache\Cache;

/**
 * This class manage the context when a user enters or exits a Learning Path.
 * TODO: Not sure the learning path ID is very useful... Maybe can be removed.
 */
final class OpignoGroupContext {
  /**
   * The key used in $_SESSION for storing the last group NID that the
   *   user visited.
   */
  const GROUP_ID = 'group_id';
  const CURRENT_CONTENT_ID = 'current_content_id';

  protected static function ensureSession() {
    if (\Drupal::currentUser()->isAnonymous()
      && !isset($_SESSION['session_started'])) {
      $_SESSION['session_started'] = TRUE;
      \Drupal::service('session_manager')->start();
    }
  }

  /**
   * Get the group ID. Can be empty.
   *
   * @return int The group context NID.
   */
  public static function getCurrentGroupId() {
    static::ensureSession();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = \Drupal::service('user.private_tempstore')
      ->get('opigno_group_manager');
    return $store->get(self::GROUP_ID);
  }

  /**
   * Get the group current Content ID (cid).
   *
   * @return int The Content ID. Can be empty.
   */
  public static function getCurrentGroupContentId() {
    static::ensureSession();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = \Drupal::service('user.private_tempstore')
      ->get('opigno_group_manager');
    return $store->get(self::CURRENT_CONTENT_ID);
  }

  /**
   * Set the context Group ID.
   *
   * @param $group_id int
   */
  public static function setGroupId($group_id) {
    static::ensureSession();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = \Drupal::service('user.private_tempstore')
      ->get('opigno_group_manager');
    $store->set(self::GROUP_ID, $group_id);
  }

  /**
   * Set the context Learning Path Content ID.
   * This method will refresh the local actions as well.
   *
   * @param $current_content_id int
   */
  public static function setCurrentContentId($current_content_id) {
    static::ensureSession();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = \Drupal::service('user.private_tempstore')
      ->get('opigno_group_manager');
    $store->set(self::CURRENT_CONTENT_ID, $current_content_id);
    self::rebuildActions();
  }

  /**
   * Remove all the context variables.
   * Refresh the local actions as well.
   */
  public static function removeContext() {
    static::ensureSession();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = \Drupal::service('user.private_tempstore')
      ->get('opigno_group_manager');
    $store->delete(self::GROUP_ID);
    $store->delete(self::CURRENT_CONTENT_ID);
    self::rebuildActions();
  }

  /**
   * Refresh the local actions.
   */
  public static function rebuildActions()
  {
    $bins = Cache::getBins();
    $bins['render']->invalidateAll();
    $bins['discovery']->invalidateAll();
  }
}
