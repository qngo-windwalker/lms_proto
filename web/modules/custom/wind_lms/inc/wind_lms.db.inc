<?php

function _wind_scorm_load_by_fid($fid){
  //  $db = \Drupal\Core\Database\Database::getConnection();
  $db = \Drupal::database();
  $query = $db->select('opigno_scorm_packages', 'o');
  $query->fields('o', []);
  $query->condition('fid', $fid);
  return $query->execute()->fetchObject();
}

function _wind_lms_get_scorm_package_title($scorm_id) {
  $db = \Drupal::database();

  $data = NULL;
  $sco = $db->select('opigno_scorm_package_scos', 'o')
    ->fields('o', [])
    ->condition('o.scorm_id', $scorm_id)
    ->execute()
    ->fetchObject();

  if ($sco) {
    return $sco->title;
  }

  return '';
}

function _wind_lms_load_all_curriculum_assigned_to_user($user){
  // https://www.drupal.org/docs/drupal-apis/entity-api/working-with-the-entity-api
  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'curriculum');
  $query->condition('status', 1);
  $query->condition('field_learner', $user->id(), 'IN');
  $result = $query->execute();

  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}

function _wind_lms_load_all_curriculum_by_access_to_all_user(){
  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'curriculum');
  $query->condition('status', 1);
  $query->condition('field_learner_access', '1');
  $result = $query->execute();

  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}

function _wind_lms_load_all_courses_assigned_to_user_by_field_learner($user){
  // https://www.drupal.org/docs/drupal-apis/entity-api/working-with-the-entity-api
  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'course');
  $query->condition('status', 1);
  $query->condition('field_learner', $user->id(), 'IN');
  $result = $query->execute();

  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}

function _wind_lms_load_all_courses_accessiable_to_user_by_field_learner_access(){
  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'course');
  $query->condition('status', 1);
  $query->condition('field_learner_access', '1');
  $result = $query->execute();

  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}

function _wind_lms_load_all_courses_accessiable_to_user_by_field_user_team(\Drupal\user\Entity\User $user) {
  // Gets all of the term tid from user
  $tids = array_map (function($item){
      return $item['target_id'];
  }, $user->get('field_team')->getValue());

  if (empty($tids)) {
    return [];
  }

  $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
  $query->condition('type', 'course');
  $query->condition('status', 1);
  $query->condition('field_user_team', $tids, 'IN');
  $result = $query->execute();

  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}

function _wind_lms_load_user_scorm_package_progress($file, $uid) {
  $db = \Drupal::database();
  /** @var \Drupal\opigno_scorm\OpignoScorm $scorm_controller */
  $scorm_controller = \Drupal::service('opigno_scorm.scorm');
  $scorm = $scorm_controller->scormLoadByFileEntity($file);
  if(!$scorm){
    return 'Unable to process SCORM package';
  }
  $data = NULL;
  $result = $db->select('opigno_scorm_scorm_cmi_data', 'o')
    ->fields('o', array('value', 'serialized'))
    ->condition('o.uid', $uid)
    ->condition('o.scorm_id', $scorm->id)
    ->condition('o.cmi_key', 'cmi.completion_status')
    ->execute()
    ->fetchObject();

  if (isset($result->value)) {
    $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    return $data;
  }

  return 'Not Started';
}

function _wind_lms_get_all_users_in_teams_by_tids($tids) {
  $result = \Drupal::entityQuery('user')
    ->condition('status', 1)
    ->condition('field_team', $tids, 'IN')
    ->execute();
  // If no result, return empty array.
  if (empty($result)) {
    return [];
  }
  return $result;
}
