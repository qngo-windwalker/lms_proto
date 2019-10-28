<?php

/**
 * Implements hook_drush_command().
 */
function wind_lms_drush_command() {
  $items = array();

  $items['add-learner'] = array(
    'callback' => 'drush_wind_lms_add_learner_callback',
    'aliases' => array('wind-lms-al')

  );

  return $items();
}

function drush_wind_lms_add_learner_callback(){
  drupal_set_message('Init creating user learner.');

  $user = User::create([
    'name' => 'tester1',
    'mail' => 'quan.windwalker@gmail.com',
    'status' => 1,
    'roles' => array('authenticated'),
  ]);
  $user->save();
}
