<?php

use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Component\Serialization\Json;

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
