<?php

use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

function _wind_lms_tincan_load_by_fid($fid){
  //  $db = \Drupal\Core\Database\Database::getConnection();
  $db = \Drupal::database();
  $query = $db->select('wind_tincan_type_properties', 'o');
  $query->fields('o', []);
  $query->condition('fid', $fid);
  return $query->execute()->fetchObject();
}

/**
 * @param $user
 * @param string $TC_COURSE_ID Can be found in the tc-config.js file located in the root of tincan course folder.
 *
 * @return string
 */
function _wind_lms_course_add_tincan_course_data($user, string $TC_COURSE_ID) {
  $agentID = _wind_lms_get_user_tincan_agent_id($user);
  if (!$agentID) {
    return [
      'progress' => 'Not Started'
    ];
  }

  $query = \Drupal::entityQuery('tincan_statement');
  $query->condition('field_tincan_actor', $agentID);
  $query->condition('field_tincan_object.id', $TC_COURSE_ID);
  $query->condition('field_tincan_object.type', 'Activity');
  $query->condition('json', 'completion', 'CONTAINS');
  // Sort latest to oldest
  $query->sort('timestamp' , 'DESC');
  $result = $query->execute();
  if (!$result) {
    $query = \Drupal::entityQuery('tincan_statement');
    $query->condition('field_tincan_actor', $agentID);
    $query->condition('field_tincan_object.id', $TC_COURSE_ID);
    $query->condition('json', 'experienced', 'CONTAINS');
    $result = $query->execute();
    if ($result) {
      return [
        'progress' => 'InComplete'
      ];
    } else {
      return [
        'progress' => 'Not Started'
      ];
    }
  }
  $statements = TincanStatement::loadMultiple($result);
  foreach ($statements as $statement ){
    $json_array = Json::decode($statement->get('json')->value);
    if( isset($json_array['result']) && isset($json_array['result']['completion']) ){
      return [
        'progress' => $json_array['result']['completion'] ? 'Completed' : 'Incomplete',
        'statement' => $statement
      ];
    }
  }

  return [
    'progress' => 'InComplete'
  ];
}


/**
 * Get Tincan Agent account_name format
 * ex: 'username|emailAddress'
 * @param $user
 *
 * @return string
 */
function _wind_lms_get_user_tincan_agent_account_name($user){
  return $user->getAccountName() . '|' . $user->getEmail();
}

/**
 * @param $user
 *
 * @return bool|integer tincan_agent id
 */
function _wind_lms_get_user_tincan_agent_id($user) {
  $agentAccountName = _wind_lms_get_user_tincan_agent_account_name($user);
  $agentQuery = \Drupal::entityQuery('tincan_agent');
  $agentQuery->condition('object_type', 'Agent');
  $agentQuery->condition('account_name', $agentAccountName);
  $result = $agentQuery->execute();
  if ($result) {
    return end($result);
  }
  return FALSE;
}

function _wind_lms_get_agent_id_by_tincan_agent_account_name($tincan_agent_account_name) {
  $agentQuery = \Drupal::entityQuery('tincan_agent');
  $agentQuery->condition('object_type', 'Agent');
  $agentQuery->condition('account_name', $tincan_agent_account_name);
  $result = $agentQuery->execute();
  if ($result) {
    return end($result);
  }
  return FALSE;
}

/**
 * This is only for the 2 test courses
 * @param $title
 * @param $course_folder
 *
 * @return array
 */
function _wind_lms_tincan_build_link_of_static_course($title, $course_folder) {
  $linkContent = '<i class="fas fa-external-link-alt align-self-center pr-1"></i> ' . "<span> {$title}</span>";
  $renderedAnchorContent = render($linkContent);
  $url = Url::fromUserInput(
    '/course/' . $course_folder,
    [
      'attributes' => [
        'data-coure-href' => _wind_lms_tincan_gen_static_course_link($course_folder),
        'class' => array('wind-scorm-popup-link', 'd-flex')
      ]
    ]
  );
  return array(
    'rendable_element' => [
      '#markup' => Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString()
    ],
    'data-coure-href' => _wind_lms_tincan_gen_static_course_link($course_folder),
    'url' => '/course/' . $course_folder,
  );
}

/**
 * @param $course_folder
 *
 * @return string
 */
function _wind_lms_tincan_gen_static_course_link($course_folder) {
  $currentUser = \Drupal::currentUser();
  $schemaAndHost = _wind_lms_get_scheme_and_http_host();

  //Ex: {"name":["quan ngo"],"account":[{"accountServiceHomePage":"http://cloud.scorm.com","accountName":"0QTP625WM3|quan.ngo@windwalker.com"}],"objectType":"Agent"}
  $actor = [
    'name' => array($currentUser->getAccountName()),
    'account' => array(
      array(
        "accountName" => _wind_lms_get_user_tincan_agent_account_name($currentUser),
        'accountServiceHomePage' => $schemaAndHost . '/user/' . $currentUser->id(),
      )
    ),
    'objectType' => 'Agent'
  ];
  $actorJson = Json::encode($actor);
  $module_handler = \Drupal::service('module_handler');
  $module_path = $module_handler->getModule('wind_lms')->getPath();
  $link = $module_path . '/course/' . $course_folder . '?';
  $link .= 'endpoint=' . $schemaAndHost . '/course/tcapi';
  $link .= '&actor=' . urlencode($actorJson);
  return $link;
}

function _wind_lms_tincan_build_course_link($title, $path, $tincan) {
  $linkContent = '<i class="fas fa-external-link-alt align-self-center pr-1"></i> ' . "<span> {$title}</span>";
  $renderedAnchorContent = render($linkContent);
  $dataCourseHref = _wind_lms_tincan_gen_course_link($path, $tincan);
  $url = Url::fromUserInput(
    $path,
    [
      'attributes' => [
        'data-course-href' => $dataCourseHref,
        'class' => array('wind-scorm-popup-link', 'd-flex')
      ]
    ]
  );
  return array(
    'rendable_element' => [
      '#markup' => Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString()
    ],
    'data-course-href' => $dataCourseHref,
    'url' => $path
  );
}

function _wind_lms_tincan_gen_course_link($path, $tincan) {
  $currentUser = \Drupal::currentUser();
  $schemaAndHost = _wind_lms_get_scheme_and_http_host();

  //Ex: {"name":["quan ngo"],"account":[{"accountServiceHomePage":"http://cloud.scorm.com","accountName":"0QTP625WM3|quan.ngo@windwalker.com"}],"objectType":"Agent"}
  $actor = [
    'name' => array($currentUser->getAccountName()),
    'account' => array(
      array(
        "accountName" => _wind_lms_get_user_tincan_agent_account_name($currentUser),
        'accountServiceHomePage' => $schemaAndHost . '/user/' . $currentUser->id(),
      )
    ),
    'objectType' => 'Agent'
  ];
  $actorJson = Json::encode($actor);
  $link = $path . '?';
  $link .= 'endpoint=' . $schemaAndHost . '/course/tcapi';
  $link .= '&actor=' . urlencode($actorJson);
  $link .= '&activity_id=' . urlencode($tincan->activity_id);
  return $link;
}
