<?php

namespace Drupal\private_message\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface defining a Private Message Thread Delation entity.
 *
 * @ingroup private_message
 */
interface PrivateMessageThreadDeleteTimeInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Set the time at which  the onwer of this entity marked the thread that references
   * this entity as deleted.
   *
   * @param int $timestamp
   *   The Unix timestamp at which the thread was marked as deleted
   */
  public function setDeleteTime($timestamp);

  /**
   * Get the time that the owner of this entity marked the thread that references
   * this entity as deleted.
   */
  public function getDeleteTime();
}
