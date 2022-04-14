<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

class WindLMSSCORMService {

  protected $database;

  /**
   * CourseNode constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function getAllCoursesByFid( $fid) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'course');
    $query->condition('field_package_file', $fid, 'IN');
    return $query->execute();
  }

}
