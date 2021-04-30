<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Drupal\wind_tincan\Entity\TincanStatement;
use Symfony\Component\HttpFoundation\JsonResponse;

class WindLMSCourseUserController extends ControllerBase{

  /**
   * wind_lms.course.user.cert.upload:
   *  path: course/{node}/user/{user}/cert/upload
   *
   * @param \Drupal\node\NodeInterface $node Course node
   * @param \Drupal\user\UserInterface $user
   *
   * @return string[]
   */
  public function getContent(NodeInterface $node, UserInterface $user){
    $header = [
      array('data' => 'First Name', 'class' => 'node-first-name-header'),
      array('data' => 'Last Name', 'class' => 'node-last-name-header'),
      array('data' => 'Email', 'class' => 'node-email-header'),
      array('data' => 'Last Login', 'class' => 'node-last-login-header'),
      array('data' => 'Last Accessed', 'class' => 'node-last-accessed-header'),
      array('data' => 'Operations', 'class' => 'node-operations-header'),
    ];

    $tablConfig = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
//          'wind_lms/course'
        ),
      )
    ];

    $packageMarkup = '';
    $field_package_file = $node->get('field_package_file');
    // If there's no zip package attached to this node,
    // declare it as ILT (Instructor Lead Training)
    if (!empty($field_package_file)) {
      /** @var \Drupal\file\Entity\File $file */
      foreach ($field_package_file->referencedEntities() as $key => $file) {
        $packageMarkup .= $this->getPackageMarkup($file, $user);
      }
    }

    $markup = '<div class="col-12-md">';
    $markup .= '<h3>Package File</h3>';
    $markup .= $packageMarkup;
//    $markup .= '<a class="btn btn-info" href="/course/1/adduser"><i class="fas fa-plus-circle"></i> Add User</a>';
    $markup .= '</div>';

    return [
      '#markup' => $markup,
    ];
  }


  public function getTitle(Node $node, UserInterface $user) {
    return  $node->label() . " :: {$user->label()}";
  }

  private function getPackageMarkup(\Drupal\file\Entity\File $file, User $user) {
    $markup = '';
    $scorm = _wind_scorm_load_by_fid($file->id());
    if ($scorm) {
      $markup  .=  '<h4>' .  _wind_lms_get_scorm_package_title($scorm->id) . '</h4>';
      $markup  .=  '<p>Type : SCORM </p>';
      $markup  .=  '<p>Filename : ' . $file->label() . '</p>';
      $markup  .=  '<p>File Id : ' . $file->id() . '</p>';
      $markup  .=  '<p>Metadata : ' . $scorm->metadata . '</p>';
      $markup  .=  '<p>Progress : ' . _wind_lms_load_user_scorm_package_progress($file, $user->id()) . '</p>';
//      $progress =  _wind_lms_load_user_scorm_package_progress($file, $user->id());
      $markup  .=  $this->getSCORMRecords($file, $user->id());
//      $data[$key]['scorm_package'] = $scorm;
//      $data[$key]['activity_link'] = wind_scorm_get_lanuch_link_rendable_array($scorm->id, $title);

    } else {
      $tincan = _wind_lms_tincan_load_by_fid($file->id());
      if ($tincan) {
        /* @var \Drupal\wind_tincan\WindTincanService $tincan_service */
        $tincan_service = \Drupal::service('wind_tincan.tincan');
        $tincan_uri = $tincan_service::getExtractPath($file);
        // Tincan XML doesn't contain any title. Resort to this for now.
        $markup  .=  '<h4>' .  str_replace('.zip', '', $file->label()) . '</h4>';
        $markup  .=  '<p>Type : Tincan </p>';
        $markup  .=  '<p>Filename : ' . $file->label() . '</p>';
        $markup  .=  '<p>File Id : ' . $file->id() . '</p>';
        $tincanProgress =  _wind_lms_course_add_tincan_course_data($user, $tincan->activity_id);
        $markup  .=  '<p>Progress : ' . $tincanProgress['progress'] . '</p>';
        $markup  .=  $this->getTincanRecords($tincan->activity_id, $user);
//        $markup  .=  $node->label();
        $path = file_url_transform_relative(file_create_url($tincan_uri)) . '/' . $tincan->launch_filename;
//        $data[$key]['tincan_package'] = $tincan;
//        $data[$key]['activity_link'] = _wind_lms_tincan_build_course_link($title, $path, $tincan);
//        $data[$key]['course_data'] = _wind_lms_course_add_tincan_course_data($user, $tincan->activity_id);
      }
    }

    return $markup;
  }

  private function getSCORMRecords(\Drupal\file\Entity\File $file, $uid) {
    /** @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
    $scorm_controller = \Drupal::service('opigno_scorm.scorm');
    $scorm = $scorm_controller->scormLoadByFileEntity($file);

    $header = [
      array('data' => 'Scorm Id', 'class' => 'node-first-name-header'),
      array('data' => 'completion_status', 'class' => 'node-last-name-header'),
      array('data' => 'Operations', 'class' => 'node-operations-header'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->getSCORMTableRows($scorm, $uid),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          //          'wind_lms/course'
        ),
      )
    ];

    return render($table);
  }

  private function getSCORMTableRows($scorm, $uid){
    $rows = array();

    $db = \Drupal::database();
    $result = $db->select('opigno_scorm_scorm_cmi_data', 'o')
      ->fields('o', array('value', 'serialized'))
      ->condition('o.uid', $uid)
      ->condition('o.scorm_id', $scorm->id)
      ->condition('o.cmi_key', 'cmi.completion_status')
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $rows = [
        'data' => array(
          'scormId' => $scorm->id,
          'value' => $result->value,
          'operations' => '',
        )
      ];
    }


    return $rows;
  }


  private function getTincanRecords($tincanActiviyId, User $user) {
    $header = [
      array('data' => 'Tincan Actor / Agent Id', 'class' => ''),
      array('data' => 'Value', 'class' => 'node-last-accessed-header'),
      array('data' => 'Operations', 'class' => 'operations-header'),
    ];

    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->getTincanTableRows($tincanActiviyId, $user),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
          //          'wind_lms/course'
        ),
      )
    ];

    return render($table);
  }

  private function getTincanTableRows($tincanActiviyId, User $user) {
    $agentID = _wind_tincan_get_user_tincan_agent_id($user);
    if (!$agentID) {
      return [];
    }

    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $tincanActiviyId);
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
    $query->condition('field_tincan_object.id', $tincanActiviyId);
    $query->condition('json', 'experienced', 'CONTAINS');
    $result = $query->execute();
    return $result;
  }

}
