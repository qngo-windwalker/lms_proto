<?php

namespace Drupal\wind_scorm\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;
use Drupal\file\Entity\File;

class WindScormPackageController extends ControllerBase{

  public function getContent() {
    $headers = [
      [
        'data' => $this->t('ID'),
      ],
      [
        'data' => 'FID',
      ],
      [
        'data' => $this->t('Extracted Dir'),
      ],
      [
        'data' => $this->t('Namifest File'),
      ],
      [
        'data' => $this->t('Namifest ID'),
      ],
      [
        'data' => $this->t('Meta Data'),
      ]
    ];

    return ['group_list' =>
      array(
        '#type' => 'table',
        '#id' => 'activities-list-table',
        '#sticky' => TRUE,
        '#weight' => 5,
        '#header' => $headers,
        '#rows' => $this->getTableRows(),
        '#empty' => t('No data found'),
      )
    ];
  }

  public function getContentById($id){
    $result = \Drupal::database()->select('opigno_scorm_packages', 'o')
      ->fields('o', [])
      ->condition('id', $id)
      ->execute()
      ->fetchAll();

    if(!$result){
      return [
        'content' => [
          '#markup' => '<p>Unable to locate SCORM package id: ' . $id . '</p>'
        ],
      ];
    }

    $rows = array();
    $markup = '';
    $scorm = $result[0];
    $rows[] = [
      $scorm->id,
      $scorm->fid,
      $scorm->extracted_dir,
//        $scorm->metadata,
    ];
    /** @var \Drupal\opigno_scorm\OpignoScormPlayer $scorm_player */
    $scorm_player = \Drupal::service('opigno_scorm.scorm_player');
    $element = $scorm_player->toRendarableArray($scorm);

    $markup .= '<p><strong>Namifest ID : </strong> ' . $scorm->manifest_id .  '</p>';
    $markup .= '<p><strong>Metadata ID : </strong>' . $scorm->metadata .  '</p>';
    $markup .= '<p><strong>Organization : </strong>' . $element['#start_sco']->organization .  '</p>';

    $headers = [
      [
        'data' => 'ID',
      ],
      [
        'data' => 'FID',
      ],
      [
        'data' => $this->t('Extracted Dir'),
      ],
    ];

    return ['table' =>
      array(
        '#type' => 'table',
        '#id' => 'scorm-package-table',
        '#sticky' => TRUE,
        '#header' => $headers,
        '#rows' => $rows,
        '#empty' => t('No data found'),
        '#attributes' => [
          'class' => array('table responsive-enabled mb-5')
        ],
      ),
      'info' => array(
        '#markup' => $markup
      ),
      'user_table' => $this->getUserTable($scorm->id),
      'launch_link' => wind_scorm_get_lanuch_link_rendable_array($scorm->id)
    ];
  }

  public function getTitleById($id){
    $element = wind_scorm_get_player_rendable_array_by_scorm_id($id);
    if(!$element){
      return 'Error: Unable to locate SCROM Package.';
    }

    return $element['#start_sco']->title;
  }

  private function getTableRows() {
    $result = \Drupal::database()->select('opigno_scorm_packages', 'o')
      ->fields('o', [])
      ->execute()
      ->fetchAll();

    if (!$result) {
      return [];
    }

    $rows = array();
    /** @var \Drupal\opigno_scorm\OpignoScorm $scorm_service */
    $scorm_service = \Drupal::service('opigno_scorm.scorm');
    foreach ($result as $obj) {
      // Convert stdClass to Array
      // @see https://stackoverflow.com/a/18576902
//      $rows[$obj->id] = json_decode(json_encode($obj), true);
      $rows[$obj->id] = array(
        $obj->id,
        $obj->fid,
        $obj->manifest_file,
        $obj->manifest_id,
        $obj->metadata,
      );
    }

    return $rows;
  }

  private function getUserTable($scorm_id) {
    $rows = array();
    $result = \Drupal::database()->select('opigno_scorm_scorm_cmi_data', 'o')
      ->fields('o', [])
//      ->condition('o.uid', $uid)
      ->condition('o.scorm_id', $scorm_id)
//      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchAll();

    $records = array();
    foreach ($result as $record){
      if($record->cmi_key == 'cmi.completion_status'){
        $records[$record->uid]['cmi.completion_status'] = $record->value;
      }

      if($record->cmi_key == 'user.sco'){
        $records[$record->uid]['user.sco'] = $record->value;
      }

      if($record->cmi_key == 'cmi.location'){
        $records[$record->uid]['cmi.location'] = $record->value;
      }
    }

    $headers = [
      [
        'data' => 'User',
      ],
      [
        'data' => 'cmi.completion_status',
      ],
      [
        'data' => 'cmi.location',
      ],
    ];
    foreach ($records as $uid => $val){
      $user = \Drupal\user\Entity\User::load($uid);
      $rows[$uid] = array(
        $user->label(),
        $val['cmi.completion_status'],
        isset($val['cmi.location']) ? $val['cmi.location'] : ''
      );
    }

    return array(
      '#type' => 'table',
      '#id' => 'scorm-package-table',
      '#sticky' => TRUE,
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No data found'),
      '#attributes' => [
        'class' => array('table responsive-enabled mb-5')
      ],
    );
  }

}
