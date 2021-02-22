<?php

namespace Drupal\wind_lms;

use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

/**
 * Class WindLMSJSONStructure.
 */
class WindLMSJSONStructure {

  protected $database;

  /**
   * OpignoScorm constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  static function getUser(User $user) {
    $field_team = $user->field_team->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $teamEntities */
    $teamEntities = array_map (function($term){
      return [
        'tid' => $term->id(),
        'label' => $term->label(),
        'vid' => $term->get('vid')->getString()
      ];
    }, $field_team);

    return [
      'uid' => $user->id(),
      'username' => $user->getAccountName(),
      'name' => $user->getAccountName(),
      'fullName' => _wind_lms_get_user_full_name($user),
      'status' => $user->get('status')->value,
      'mail' => $user->get('mail')->value,
      'access' => $user->get('access')->value,
      'login' => $user->get('login')->value,
      'field_team' => $teamEntities,thit
    ];
  }

}
