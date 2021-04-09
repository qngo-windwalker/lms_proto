<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Class CertificateNode.
 */
class CertificateNode {

  protected $database;

  /**
   * CourseNode constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * @param int $courseNid
   * @param int $uid
   *
   * @return mixed|bool|string
   */
  static function loadCertNode($courseNid, $uid) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'certificate');
    $query->condition('status', 1);
    $query->condition('field_activity', $courseNid);
    $query->condition('field_learner', $uid);
    $result = $query->execute();
    if($result) {
      return \Drupal\node\Entity\Node::load(array_shift($result));
    }

    return false;
  }
}
