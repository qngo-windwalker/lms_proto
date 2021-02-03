<?php

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

function _wind_lms_load_all_courses_assigned_to_user($user){
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

function _wind_lms_load_all_courses_by_access_to_all_user(){
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
