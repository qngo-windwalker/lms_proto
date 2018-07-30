<?php

namespace Drupal\opigno_learning_path;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a LatestActivity entity.
 *
 * @ingroup opigno_learning_path
 */
interface LatestActivityInterface extends ContentEntityInterface {

  /**
   * Gets the training ID.
   *
   * @return int
   *   The training ID.
   */
  public function getTraining();

  /**
   * Sets the training ID.
   *
   * @param int $id
   *   The training ID.
   *
   * @return \Drupal\opigno_learning_path\LatestActivityInterface
   *   The called entity.
   */
  public function setTraining($id);

  /**
   * Gets the module ID.
   *
   * @return int
   *   The module ID.
   */
  public function getModule();

  /**
   * Sets the module ID.
   *
   * @param int $id
   *   The module ID.
   *
   * @return \Drupal\opigno_learning_path\LatestActivityInterface
   *   The called entity.
   */
  public function setModule($id);

  /**
   * Gets the timestamp.
   *
   * @return int
   *   The timestamp.
   */
  public function getTimestamp();

  /**
   * Sets the timestamp.
   *
   * @param int $value
   *   The timestamp.
   *
   * @return \Drupal\opigno_learning_path\LatestActivityInterface
   *   The called entity.
   */
  public function setTimestamp($value);

}
