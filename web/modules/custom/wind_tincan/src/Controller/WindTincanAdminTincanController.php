<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\wind_tincan\Entity\TincanState;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;

class  WindTincanAdminTincanController extends ControllerBase{

  /**
   * route: wind_tincan.admin.tincan:
   *  path: '/admin/tincan'
   * @param string $uid
   */
  public function getContent() {
    return [
      '#markup' => '<div><table id="tincan-tbl" ref="main" data-striping="1"></table></div>',
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_tincan',
        ),
        'drupalSettings' => array(
          'wind_tincan' => array(
            'datatableData' => $this->getAllUserData(),
          ),
        ),
      ),
    ];
  }

  /**
   * route: wind_tincan.admin.tincan.user.course:
   *  path: '/admin/tincan/{uid}/course/{tincan_id}'
   * @deprecated use getUserNodeContent
   * @param string $uid
   * @param string $tincan_id
   */
  public function getUserCourseContent($uid, $tincan_id) {
    $user = User::load($uid);
    $TincanIdDecoded = base64_decode($tincan_id);
    $info = "<h2>Username: {$user->label()} -- Uid: {$user->id()}</h2>";
    return [
      '#markup' => $info . '<div><table id="tincan-user-course-tbl" ref="main" data-striping="1"></table></div>',
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_tincan_user_course',
        ),
        'drupalSettings' => array(
          'wind_tincan' => array(
            'datatableData' => $this->getTincan($user, $TincanIdDecoded),
          ),
        ),
      ),
    ];
  }

  /**
   * wind_tincan.admin.tincan.user.uid.node.nid:
   *   path: '/admin/tincan/user/{user}/node/{node}'
   *
   * @param \Drupal\user\Entity\User $user
   * @param \Drupal\node\Entity\Node $node
   *
   * @return array
   */
  public function getUserNodeContent(User $user, Node $node) {
    $field_package_file = $node->get('field_package_file');
    $courseData = [];
    $courseData['field_package_file'] = array();

    if (!empty($field_package_file)) {
      /** @var \Drupal\file\Entity\File $file */
      foreach ($field_package_file->referencedEntities() as $key => $file) {
        $courseData['field_package_file'][$file->id()] = $this->getUserFileContent($user, $file);
      }
    }

    $nodeInfo = array(
      '#type' => 'table',
      '#header' => array('nid', 'Title'),
      '#rows' => [ array(
        $node->id(),
        new FormattableMarkup("<a href='/node/{$node->id()}'>@title</a>", ['@title' => $node->label()]),
      )],
      '#empty' => t('There are no data available.'),
    );

    return [
      'node' => $nodeInfo,
      'field_package_file' => $courseData['field_package_file'],
    ];
  }

  public function getUserFileContent(User $user, File $file) {
    $agentID = _wind_tincan_get_tincan_agent_id_by_user($user);
    if (!$agentID) {
      return [ '#markup' => 'Unable to get tincan_agent_id for user.'];
    }
    $tincan = _wind_lms_tincan_load_by_fid($file->id());
    if (!$tincan) {
      return [ '#markup' => 'Unable to load tincan by file id.'];
    }

    return [
      'user' => $this->getUserInfoTableElement($user),
      'field_package_file' => $this->getFileInfoTableElement($file, $user, $tincan),
      'tincanStatementTitle' => array('#markup' => '<h3>Tincan Statement</h3>'),
      'datatable' => array(
        '#markup' => '<div><table id="tincan-user-course-tbl" ref="main" data-striping="1" data-page-length="25"></table></div>',
        '#attached' => array(
          'library' => array(
            'wind_tincan/admin_tincan_user_node',
            'wind/code_highlight',
          ),
          'drupalSettings' => array(
            'wind_tincan' => array(
              'datatableData' => [ 'tincanStatement' => $this->getTincanRecords($tincan->activity_id, $agentID)]
            ),
          ),
        ),
      ),
      'divider' => array('#markup' => '<div><br /><hr /><hr /><br /></div>'),
      'tincanStateTitle' => array('#markup' => '<h3>Tincan State</h3>'),
      'tincanState' => $this->getUserTincanStateDatatable($agentID, $tincan->activity_id),
    ];
  }

  public function getUserCourseTitle($uid, $tincan_id) {
    $user = User::load($uid);
    $TincanIdDecoded = base64_decode($tincan_id);
    $courses = _wind_tincan_get_user_all_assigned_course_data($user);
    foreach ($courses as $course) {
      if ($course['tincan_course_id'] == $TincanIdDecoded) {
        return $course['title'];
      }
    }
  }

  public function getStatement($id) {
    $destination = \Drupal::request()->query->get('destination');
    $destinationParams = $destination ? "destination=${destination}" : '';
    $statement = TincanStatement::load($id);
    if ($statement == NULL) {
      return [
        '#markup' => 'Unable to load this statement',
      ];
    }
    $rows = [];
    $rows[] = [
      $statement->id(),
      $statement->get('statement_id')->value,
      date('D d\t\h\ M Y', $statement->get('stored_date')->value),
      $statement->get('timestamp')->value,
      $statement->get('field_tincan_actor')->getString(),
      new FormattableMarkup('<pre><code>@codes</code></pre>', ['@codes' => $statement->get('field_tincan_object')->getString()]),
    ];
    return [
      'operation' => array(
        '#markup' => "<p><a href='/admin/tincan/statement/${id}/delete?${destinationParams}' class='action-link action-link--danger action-link--icon-trash'>Delete</a></p>",
      ),
      'table' => array(
        '#type' => 'table',
        '#header' => array('id', 'statement_id', 'stored_date', 'timestamp', 'field_tincan_actor', 'field_tincan_object'),
        '#rows' => $rows,
        '#empty' => t('There are no data available.'),
      ),
      'json' => array(
        '#markup' => '<div><h5>json</h5><pre><code class="language-json">' . json_encode(Json::decode($statement->get('json')->value), JSON_PRETTY_PRINT) . '</code></pre></div>',
        '#attached' => array(
          'library' => array(
            'wind/code_highlight',
          ),
        ),
      ),
    ];
  }

  private function getUserTincanStateDatatable($agentID, $tincanActiviyId) {
    return [
      '#markup' => '<div><table id="tincan-user-tincan-state-tbl" ref="main" data-striping="1" data-page-length="20"></table></div>',
      '#attached' => array(
        'library' => array(
          'wind_tincan/admin_tincan_user_tincan_state',
          'wind/code_highlight',
        ),
        'drupalSettings' => array(
          'wind_tincan' => array(
            'datatableData' => [ 'tincanState' => $this->getLearnerTincanState($tincanActiviyId, $agentID) ],
          ),
        ),
      ),
    ];
  }

  private function getUserInfoTableElement(User $user) {
    return array(
      '#type' => 'table',
      '#header' => array('username', 'uid', 'Email'),
      '#rows' => [
        array( $user->label(), $user->id(), $user->getEmail()),
      ],
      '#empty' => t('There are no data available.'),
    );
  }

  private function getFileInfoTableElement(File $file, User $user, $tincan) {
    $currentUri = \Drupal::request()->getRequestUri();
    $rows = [];
    $rows[] = [
      $file->id(),
      new FormattableMarkup("<a href='/admin/tincan/user/{$user->id()}/file/{$file->id()}?destination={$currentUri}'>{$file->label()}</a>", []),
      $tincan->activity_id,
      new FormattableMarkup("<a href='/admin/tincan/user/@uid/file/{$file->id()}/delete-all-statements?destination={$currentUri}' class='action-link action-link--danger action-link--icon-trash'>Delete All Statements</a>", ['@uid' => $user->id()]),
    ];
    return array(
      '#type' => 'table',
      '#header' => array('fid', 'filename', 'tincan activity_id', 'Operations'),
      '#rows' => $rows,
      '#empty' => t('There are no data available.'),
    );
  }

  private function getAllUserData() {
    $data = [];
    $users = User::loadMultiple();
    foreach ($users as $user) {
      if ($user->id() == 0) {
        continue;
      }
      $data[] = [
        'user' => $this->getUserData($user),
        'courses' => $this->getUserCourses($user),
      ];
    }
    return $data;
  }

  private function getUserData(User $user) {
    return [
      'uid' => $user->id(),
      'username' => $user->label(),
      'agentId' => _wind_tincan_get_user_tincan_agent_id($user),
    ];
  }

  private function getTincanRecords($tincanActiviyId, $agentID) {
    $result = _wind_tincan_get_all_statements_by_activity_id_and_agent_id($tincanActiviyId, $agentID);
    if (!$result) {
      return [];
    }

    $records = array();
    $statements = TincanStatement::loadMultiple($result);
    foreach ($statements as $statement ){
      $json_array = Json::decode($statement->get('json')->value);
      $records[] = array(
        'statementJsonId' => $json_array['id'],
        'statementJsonTimestamp' => strtotime($json_array['timestamp']),
        'id' => $statement->id(),
        'statementId' => $statement->get('statement_id')->value,
        'verb' => $json_array['verb']['display'],
        'context' => isset($json_array['context']) ? $json_array['context'] : [],
        'object' => $json_array['object'],
        'result' => isset($json_array['result']) ? $json_array['result'] :  'No data',
      );
    }
    return $records;
  }

  private function getTincan($user, $TC_COURSE_ID) {
    $agentID = _wind_tincan_get_user_tincan_agent_id($user);
    if (!$agentID) {
     return [];
    }

    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $TC_COURSE_ID);
    $query->condition('field_tincan_object.type', 'Activity');
    $query->condition('json', 'completion', 'CONTAINS');
    // Sort latest to oldest
    $query->sort('timestamp' , 'DESC');
    $result = $query->execute();
    if ($result) {
      $statementsDecoded = [];
      $statements = TincanStatement::loadMultiple($result);
      foreach ($statements as $statement ){
        $json_array = Json::decode($statement->get('json')->value);
        $statementsDecoded[] = $json_array;
      }
      return $statementsDecoded;
    }

    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $TC_COURSE_ID);
    $query->condition('json', 'experienced', 'CONTAINS');
    $result = $query->execute();
    return $result;
  }

  private function getUserCourses(User $user) {
    $courses = _wind_tincan_get_user_all_assigned_course_data($user);
    foreach ($courses as &$course) {
      $course['tincan_statements'] = $this->getTincan($user, $course['tincan_course_id']);
    }
    return $courses;
  }

  private function getLearnerTincanState($tincanActiviyId, $agentID) {
    $query = \Drupal::entityQuery('tincan_state');
    $query->condition('field_tincan_agent', $agentID);
    $query->condition('activity_id', $tincanActiviyId);
    // Sort latest to oldest
    $query->sort('updated' , 'DESC');
    $result = $query->execute();
    if (!$result) {
      return [];
    }

    $records = array();
    $states = TincanState::loadMultiple($result);
    /** @var \Drupal\wind_tincan\Entity\TincanState $state */
    foreach ($states as $state ){
      $records[] = array(
        'id' => $state->id(),
        'state_id' => $state->get('state_id')->value,
        'activity_id' => $state->get('activity_id')->value,
        'stored_date' => $state->get('stored_date')->value,
        'updated' => $state->get('updated')->value,
        'contents' =>  $state->get('contents')->value,
      );
    }
    return $records;
  }

}
