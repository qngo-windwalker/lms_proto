<?php

namespace Drupal\opigno_module\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Module entity.
 *
 * @ingroup opigno_module
 *
 * @ContentEntityType(
 *   id = "opigno_module",
 *   label = @Translation("Module"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\opigno_module\OpignoModuleListBuilder",
 *     "views_data" = "Drupal\opigno_module\Entity\OpignoModuleViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\opigno_module\Form\OpignoModuleForm",
 *       "add" = "Drupal\opigno_module\Form\OpignoModuleForm",
 *       "edit" = "Drupal\opigno_module\Form\OpignoModuleForm",
 *       "delete" = "Drupal\opigno_module\Form\OpignoModuleDeleteForm",
 *     },
 *     "access" = "Drupal\opigno_module\OpignoModuleAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\opigno_module\OpignoModuleHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "opigno_module",
 *   revision_table = "opigno_module_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer module entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/module/{opigno_module}",
 *     "add-form" = "/admin/structure/opigno_module/add",
 *     "edit-form" = "/admin/structure/opigno_module/{opigno_module}/edit",
 *     "delete-form" = "/admin/structure/opigno_module/{opigno_module}/delete",
 *     "collection" = "/admin/structure/opigno_module",
 *   },
 *   field_ui_base_route = "opigno_module.settings"
 * )
 */
class OpignoModule extends RevisionableContentEntityBase implements OpignoModuleInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'uid' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * Get Module Randomization setting.
   */
  public function getRandomization() {
    return $this->get('randomization')->value;
  }

  /**
   * Get Module Backwards navigation setting.
   */
  public function getBackwardsNavigation() {
    return (bool) $this->get('backwards_navigation')->value;
  }

  /**
   * Get Module Always available setting.
   */
  public function getModuleAlways() {
    return (bool) $this->get('module_always')->value;
  }

  /**
   * Get Module open date.
   */
  public function getOpenDate() {
    return $this->get('open_date')->value;
  }

  /**
   * Get Module close date.
   */
  public function getCloseDate() {
    return $this->get('close_date')->value;
  }

  /**
   * Get Allow resume setting.
   */
  public function getAllowResume() {
    return (bool) $this->get('allow_resume')->value;
  }

  /**
   * Get random activities count.
   */
  public function getRandomActivitiesCount() {
    return $this->get('random_activities')->value;
  }

  /**
   * Set random activities count.
   */
  public function setRandomActivitiesCount($value) {
    $this->set('random_activities', $value);
    return $this;
  }

  /**
   * Get random activity score.
   */
  public function getRandomActivityScore() {
    return $this->get('random_activity_score')->value;
  }

  /**
   * Set random activities count.
   */
  public function setRandomActivityScore($value) {
    $this->set('random_activity_score', $value);
    return $this;
  }


  /**
   * Get image entity.
   */
  public function getModuleImage() {
    return $this->get('module_image')->entity;
  }

  /**
   * Get feedback results options.
   */
  public function getResultsOptions() {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $select_query = $connection->select('opigno_module_result_options', 'omro')
      ->fields('omro')
      ->condition('module_id', $this->id())
      ->condition('module_vid', $this->getRevisionId());
    $options = $select_query->execute()->fetchAll();
    return $options;
  }

  /**
   * Insert feedback results options.
   */
  public function insertResultsOptions(FormStateInterface $form_state) {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $insert_query = $connection->insert('opigno_module_result_options')
      ->fields([
        'module_id',
        'module_vid',
        'option_name',
        'option_summary',
        'option_summary_format',
        'option_start',
        'option_end',
      ]);
    $form_values = $form_state->getValues();
    foreach ($form_values['results_options'] as $delta => $option) {
      if (!empty($option['option_name'])) {
        if (is_array($option['option_summary'])) {
          $option['option_summary_format'] = $option['option_summary']['format'];
          $option['option_summary'] = $option['option_summary']['value'];
        }
        $insert_query->values(array(
          'module_id' => $this->id(),
          'module_vid' => $this->getRevisionId(),
          'option_name' => $option['option_name'],
          'option_summary' => $option['option_summary'],
          'option_summary_format' => $option['option_summary_format'],
          'option_start' => $option['option_start'],
          'option_end' => $option['option_end'],
        ));
      }
    }
    $insert_query->execute();
  }

  /**
   * Update feedback results options.
   */
  public function updateResultsOptions(FormStateInterface $form_state) {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    // Remove existing options.
    $delete_query = $connection->delete('opigno_module_result_options')
      ->condition('module_id', $this->id())
      ->condition('module_vid', $this->getRevisionId())
      ->execute();
    // Insert options.
    $this->insertResultsOptions($form_state);
  }

  public function checkModuleAvailability() {
    $availability = [
      'open' => FALSE,
      'message' => '',
    ];
    if ($this->getModuleAlways()) {
      $availability['open'] = TRUE;
    }
    else {
      $quiz_open = \Drupal::time()->getRequestTime() >= $this->getOpenDate();
      $quiz_closed = \Drupal::time()->getRequestTime() >= $this->getCloseDate();
      if (!$quiz_open || $quiz_closed) {
        if ($quiz_closed) {
          $availability = [
            'open' => FALSE,
            'message' => t('This "@module" module is closed.', array('@module' => $this->getName())),
          ];
        }
        if (!$quiz_open) {
          $availability = [
            'open' => FALSE,
            'message' => t('This "@module" module is not yet open.', array('@module' => $this->getName())),
          ];
        }
      }
    }
    return $availability;
  }

  /**
   * Get loaded statuses for specified user.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User entity object.
   */
  public function getModuleAttempts(AccountInterface $user) {
    $status_storage = static::entityTypeManager()->getStorage('user_module_status');
    $query = $status_storage->getQuery();
    $module_statuses = $query
      ->condition('module', $this->id())
      ->condition('user_id', $user->id())
      ->execute();
    return $status_storage->loadMultiple($module_statuses);
  }

  /**
   * Get entity if user didn't finish module.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User entity object.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getModuleActiveAttempt(AccountInterface $user) {
    $status_storage = static::entityTypeManager()->getStorage('user_module_status');
    $query = $status_storage->getQuery();
    $module_statuses = $query
      ->condition('module', $this->id())
      ->condition('user_id', $user->id())
      ->condition('finished', 0)
      ->execute();
    return !empty($module_statuses) ? $status_storage->load(key($module_statuses)) : NULL;
  }

  /**
   * Get activities related to specific module.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModuleInterface $opigno_module
   *   Opigno module entity object.
   *
   * @return array
   *   Array of module's activities.
   */
  public function getModuleActivities() {
    /* @todo join table with activity revisions */
    $activities = [];
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $query = $db_connection->select('opigno_activity', 'oa');
    $query->fields('oa', ['id', 'vid', 'type', 'name']);
    $query->fields('omr', [
      'activity_status',
      'weight', 'max_score',
      'auto_update_max_score',
      'omr_id',
      'omr_pid',
      'child_id',
      'child_vid',
    ]);
    $query->addJoin('inner', 'opigno_module_relationship', 'omr', 'oa.id = omr.child_id');
    $query->condition('oa.status', 1);
    $query->condition('omr.parent_id', $this->id());
    if ($this->getRevisionId()) {
      $query->condition('omr.parent_vid', $this->getRevisionId());
    }
    $query->condition('omr_pid', NULL, 'IS');
    $query->orderBy('omr.weight');
    $result = $query->execute();
    foreach ($result as $activity) {
      $activities[$activity->id] = $activity;
    }

    return $activities;
  }

  /**
   * Get answers of the specific user within specified attempt.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   * @param \Drupal\opigno_module\Entity\UserModuleStatusInterface $attempt
   *
   * @return array|\Drupal\Core\Entity\EntityInterface|null
   */
  public function userAnswers(AccountInterface $user, UserModuleStatusInterface $attempt) {
    $answers_storage = static::entityTypeManager()->getStorage('opigno_answer');
    $query = $answers_storage->getQuery();
    $answers = $query
      ->condition('module', $this->id())
      ->condition('user_id', $user->id())
      ->condition('user_module_status', $attempt->id())
      ->execute();
    return !empty($answers) ? $answers_storage->loadMultiple(array_keys($answers)) : [];
  }

  /**
   * @param \Drupal\opigno_module\Entity\UserModuleStatusInterface $attempt
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getRandomActivity(UserModuleStatusInterface $attempt) {
    // Need to get activity that was not answered yet in this attempt.
    // Take all the activities.
    $activities = $this->getModuleActivities();
    $activities_storage = static::entityTypeManager()->getStorage('opigno_activity');
    $randomization = $this->getRandomization();
    $random_count = $this->getRandomActivitiesCount();
    $answered_random = 0;
    // Take answers within attempt.
    $user_answers = $this->userAnswers(\Drupal::currentUser(), $attempt);
    if (!empty($user_answers)) {
      foreach ($user_answers as $answer) {
        $answer_activity = $answer->getActivity();
        // Unset answered activity if answer already exist.
        if (isset($activities[$answer_activity->id()])) {
          if ($activities[$answer_activity->id()]->activity_status == 0) {
            $answered_random++;
          }
          unset($activities[$answer_activity->id()]);
        }
      }
    }
    if ($randomization == 2) {
      $assigned_random = [];
      foreach ($activities as $id => $activity) {
        if ($activity->activity_status == 0) {
          $assigned_random[$id] = $activity;
          unset($activities[$id]);
        }
      }
      if (!empty($assigned_random) && $random_count > $answered_random) {
        $required_random = $random_count - $answered_random;
        $random_activities = array_rand($assigned_random, $required_random);
        if (is_array($random_activities)) {
          foreach ($random_activities as $random_activity) {
            $activities[$random_activity] = $assigned_random[$random_activity];
          }
        }
        else {
          $activities[$random_activities] = $assigned_random[$random_activities];
        }
      }
    }
    return !empty($activities) ? $activities_storage->load(array_rand($activities, 1)) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Module entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Module entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['module_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('Set here a module image'))
      ->setTranslatable(FALSE)
      ->setRequired(FALSE)
      ->setSettings([
      'alt_field' => FALSE,
      'alt_field_required' => FALSE,
    ])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'image',
        'weight' => 4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDefaultValue('')
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setRequired(FALSE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'text_long',
        'weight' => 3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Module is published.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the Module was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the Module was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['random_activities'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Random activities count'))
      ->setDescription(t('Random activities count for this Module.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0);

    $fields['random_activity_score'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Random activity score'))
      ->setDescription(t('Score per each random activity.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(1);

    $fields['allow_resume'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Allow resume'))
      ->setDescription(t('Allow users to leave this Module incomplete and then resume it from where they left off.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => 1,
      ));

    $fields['backwards_navigation'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Backwards navigation'))
      ->setDescription(t('Allow users to go back and revisit activities already answered.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => 2,
      ));

    $randomization_options = [
      0 => t('No randomization'),
      1 => t('Random order'),
      2 => t('Random questions'),
    ];
    $randomization_description = t("<strong>Random order</strong> - all questions display in random order")
      . '<br/>' . t("<strong>Random questions</strong> - specific number of questions are drawn randomly from this module's pool of questions");
    $fields['randomization'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Randomize activities'))
      ->setDescription($randomization_description)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('allowed_values', $randomization_options)
      ->setDefaultValue(0)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => 3,
      ));

    $takes_options = array(t('Unlimited'));
    for ($i = 1; $i < 10; $i++) {
      $takes_options[$i] = $i;
    }
    $fields['takes'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Allowed number of attempts'))
      ->setDescription(t('The number of times a user is allowed to take this Module. <strong>Anonymous users are only allowed to take Module that allow an unlimited number of attempts.</strong>'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('allowed_values', $takes_options)
      ->setDefaultValue(0)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => 4,
      ));

    $fields['show_attempt_stats'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Display allowed number of attempts'))
      ->setDescription(t('Display the allowed number of attempts on the starting page for this Module.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => 5,
      ));

    $keep_results_options = [
      0 => t('The best'),
      1 => t('The newest'),
      2 => t('All'),
    ];
    $fields['keep_results'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Store results'))
      ->setDescription(t('These results should be stored for each user.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setSetting('allowed_values', $keep_results_options)
      ->setDefaultValue(2)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => 6,
      ));

    $fields['module_always'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Always available'))
      ->setDescription(t('Ignore the open and close dates.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => 1,
      ))
      ->setDefaultValue(TRUE);

    $fields['open_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Open date'))
      ->setDescription(t('The date this Module will become available.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 2,
      ));

    $fields['close_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Close date'))
      ->setDescription(t('The date this Module will become unavailable.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 3,
      ));

    return $fields;
  }

}
