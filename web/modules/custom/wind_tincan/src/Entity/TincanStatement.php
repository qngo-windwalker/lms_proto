<?php

namespace Drupal\wind_tincan\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Defines the tincan_statement entity.
 *
 * @ingroup tincan_statement
 *
 * @ContentEntityType(
 *   id = "tincan_statement",
 *   label = @Translation("Tincan Statement"),
 *   base_table = "tincan_statement",
 *   entity_keys = {
 *     "id" = "id",
 *     "statement_id" = "statement_id",
 *   },
 *   config_export = {
 *     "id",
 *     "statement_id",
 *   }
 * )
 */
class TincanStatement extends ContentEntityBase implements ContentEntityInterface {

  private $notation;
  private $object_type;

  /**
   * @see https://www.drupal.org/docs/8/api/entity-api/fieldtypes-fieldwidgets-and-fieldformatters
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *
   * @return array|\Drupal\Core\Field\FieldDefinitionInterface[]|mixed
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the TincanStatement entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['statement_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('statement_id'))
      ->setDescription(t('The Statement ID of the TincanStatement entity.'))
      ->setSettings(array(
        'max_length' => 64,
        'text_processing' => 0,
      ))
      ->setReadOnly(TRUE);

    $fields['stored_date'] = BaseFieldDefinition::create('created')
      ->setLabel(t('stored_date'))
      ->setDescription(t('The Unix timestamp when the invite was created.'));

    $fields['timestamp'] = BaseFieldDefinition::create('string')
      ->setLabel(t('timestamp'))
      ->setDescription(t('The Unix timestamp when the events described within the statement occurred.'))
      ->setSettings(array(
        'max_length' => 75,
      ));

    // @see https://www.drupal.org/docs/8/api/entity-api/fieldtypes-fieldwidgets-and-fieldformatters
    // @see example: https://www.drupal8.ovh/en/tutoriels/263/custom-content-entity-field-types-of-drupal-8
    // Todo: Code below will create longblob (4GB). If possible, reduce it to 'blob:normal' => 'BLOB'. @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Driver%21mysql%21Schema.php/function/Schema%3A%3AgetFieldTypeMap/8.1.x
    $fields['json'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('json'))
      ->setSetting('case_sensitive', TRUE)
      ->setDescription(t('The JSON data for this entity.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $values = []) {
    /** @var \Drupal\wind_tincan\Entity\TincanStatement $statement */
    $statement = parent::create($values);
    if(isset($values['json'])){
      $statement->notation = $values['json'];
      $statement->populateEntityValues();
    }
    return $statement;
  }

  /**
   * Populates the properties and fields for the entity from the decoded entity JSON
   */
  public function populateEntityValues() {
    if($this->notation == '') return FALSE;
    if($this->validateJSON()) {
      try {
        $this->populate();
      } catch(Exception $e) {
        throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
//        services_error ('Internal Server Error', 500,  $e->getMessage());
      }
    }
  }

  /**
   * Validates the JSON for the entity
   *
   * @return Boolean
   *   Returns TRUE if the JSON validates, otherwise FALSE
   */
  function validateJSON() {
    if(!isset($this->notation) || $this->notation == '') return FALSE;
    return _wind_tincan_json_validation($this->notation,'tincan_statement entity validation');
  }

  /**
   * Populates the entity properties and fields from set entity JSON
   */
  private function populate() {
    $json = $this->notation;
    $json_array = Json::decode($json);

    // object_type
    if (isset($json_array['objectType']) && $json_array['objectType']=='SubStatement') {
      $this->object_type = 'SubStatement';
      if (isset($json_array['version'])) {
        unset($json_array['version']);
      }
      if (isset($json_array['id'])) {
        unset($json_array['id']);
      }
      if (isset($json_array['stored'])) {
        unset($json_array['stored']);
      }
      if (isset($json_array['authority'])) {
        unset($json_array['authority']);
      }
    } else {
      $this->object_type = 'Statement';
    }
    // version
    if(isset($json_array['version'])) {
      $this->version = $json_array['version'];
    } else {
      if($this->object_type != 'SubStatement') {
        $this->version = '1.0.0';
      }
    }
    // id
    // commentted out b/c TincanStatement::create($tincan_statement) will have id. Might come back to this later
//    if(isset($json_array['id'])) {
//      $this->statement_id = $json_array['id'];
//    }
//    else { // generate uuid for the statement
//      if($this->object_type != 'SubStatement' && !isset($this->statement_id)) {
//        $this->statement_id = uuid_generate();
//      }
//    }

    // timestamp
    if(isset($json_array['timestamp'])) {
      // Set tincan_statement.timestamp db column
      $this->timestamp = $json_array['timestamp'];
    }
    // stored
    // Commetted out: Already available in instantiation TincanStatement::create($tincan_statement)
//    if(isset($json_array['stored'])) {
//      // $this->stored = $json_array['stored'];
//      // thinking the LRS should always set the stored value
//      if($this->object_type != 'SubStatement') {
//        $this->stored_date = date('c'); //must be in iso 8601 format;
//      }
//    } else {
//      if($this->object_type != 'SubStatement') {
//        $this->stored_date = date('c'); //must be in iso 8601 format;
//      }
//    }

    // Actor
    if(isset($json_array['actor'])) {
      $this->tincan_actor = array();
      $this->populateActor($json_array['actor']);
    }
    // Verb
    if(isset($json_array['verb'])) {
      $this->tincan_verb = array();
      // Todo: Add Verb
//      $this->populateVerb($json_array['verb']);
    }

    // Object
    if(isset($json_array['object'])) {
      $this->tincan_object = array();
      $this->populateObject($json_array);
    }
    // Result
    if (isset($json_array['result'])) {
      $this->tincan_result = array();
      // Todo Add result
//      $this->populateResult($json_array['result']);
    }
    // Authority
    // https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#authority
    // "The LRS SHOULD overwrite the authority on all stored received Statements, based on the credentials used to send those Statements."
    // if there is already an authority, we should overwrite it if the user is not anonymous that was used to access the api
    // the rewriting of the authority will cause a difference between the sent statement, and the stored statement.
//    if (!user_is_anonymous() && $this->object_type != 'SubStatement') {
//      global $user;
//      $authority = array();
//      $authority['objectType'] = 'Agent';
//      $authority['name'] = $user->name;
//      $authority['mbox'] = $user->mail;
//      $json_array['authority'] = $authority;
//    }

//    if (isset($json_array['authority']) ) {
//      $this->populateAuthority($json_array['authority']);
//    }
//
//    if (isset($json_array['context'])) {
//      $this->tincan_context = array();
//      $this->populateContext($json_array['context']);
//    }
//
//    if (isset($json_array['attachments'])) {
//      $this->tincan_statement_attachment = array();
//      $this->populateAttachments($json_array['attachments']);
//    }
  } //end of populate method

  /**
   * Populates the tincan_actor field from a decoded JSON array
   *
   * @param array $json_array_actor
   *   Array of decoded JSON values for the actor object
   */
  private function populateActor($json_array_actor) {
    $target_id = $this->findAgent($json_array_actor);

    if (!$target_id) {
      $target_id = $this->createAgent($json_array_actor);
    }
    if ($target_id) {
      $this->field_tincan_actor->target_id = $target_id;
    }
  }

  /**
   * Populates the tincan_verb field from a decoded JSON array
   *
   * @param array $json_array
   *   Array of decoded JSON values for the verb object
   */
  private function populateVerb($json_array) {
    if (isset($json_array['id'])) {
      $this->tincan_verb[LANGUAGE_NONE][0]['id'] = $json_array['id'];
    }
    if (isset($json_array['display']['en-US'])) {
      $this->tincan_verb[LANGUAGE_NONE][0]['display_en_us'] = $json_array['display']['en-US'];
    }
    if (isset($json_array['display'])) {
      $this->tincan_verb[LANGUAGE_NONE][0]['display'] = drupal_json_encode($json_array['display']);
    }

    $this->tincan_verb[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }

  public function findAgent($json_array) {
    if (!isset($json_array['mbox']) &&
      !isset($json_array['mbox_sha1sum']) &&
      !isset($json_array['openid']) &&
      (!isset($json_array['account']) && !isset($json_array['account']['homePage']) && ! isset($json_array['account']['name'])) ) {
      return 0;
    }

    $query = \Drupal::entityQuery('tincan_agent');
    if (isset($json_array['objectType'])) {
      switch ($json_array['objectType']) {
        case 'Agent':
          $query->condition('object_type','Agent');
          break;
        case 'Group':
          $query->condition('object_type','Group');
          break;
      }
    } else {
      $query->condition('object_type','Agent');
    }
    $ifi_found = 0;
    if (isset($json_array['mbox']) && !$ifi_found) {
      $query->condition('mbox',$json_array['mbox']);
      $ifi_found = 1;
    }
    if (isset($json_array['mbox_sha1sum']) && !$ifi_found) {
      $query->condition('mbox_sha1sum',$json_array['mbox_sha1sum']);
      $ifi_found = 1;
    }
    if (isset($json_array['openid']) && !$ifi_found) {
      $query->condition('openid',$json_array['openid']);
      $ifi_found = 1;
    }
    if (isset($json_array['account']) && isset($json_array['account']['homePage']) && isset($json_array['account']['name']) && !$ifi_found) {
      $query->condition('account_home_page',$json_array['account']['homePage']);
      $query->condition('account_name',$json_array['account']['name']);
      $ifi_found = 1;
    }
    $result = $query->execute();
    // return the last value of the array.
    return $result ? end($result) : FALSE ;
  }

  /**
   * Creates an agent entity
   *
   * @param array $json_array
   *   String with a JSON Agent Object
   *
   * @return integer
   *   Returns the entity id of the agent if created successfully, otherwise 0;
   */
  function createAgent($json_array) {
    $values = array();
    $values['json'] = Json::encode($json_array);

    $tincan_agent_entity = TincanAgent::create($values);

    try {
      $save_result = $tincan_agent_entity->save();
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
      //      services_error ('Internal Server Error', 500, 'Statement save fail: ' . $e->getMessage());
    }

    return $save_result ? $tincan_agent_entity->id() : FALSE;
  }

  /**
   * Populates the tincan_object field from a decoded JSON array
   *
   * @param array $json_array
   *   Array of decoded JSON values for the 'object' object
   */
  private function populateObject($json_array) {
    $target_id = 0;
    if (isset($json_array['objectType'])) {
      $this->field_tincan_object->type = $json_array['objectType'];
      switch ($json_array['objectType']) {
        case 'Activity':
          $this->field_tincan_object->table = 'tincan_activity';
          $target_id = $this->findActivity($json_array);
          if (!$target_id) {
            $target_id = $this->createActivity($json_array);
          }
          break;
        case 'Agent':
        case 'Group':
          $this->field_tincan_object->table = 'tincan_agent';
          $target_id = $this->findAgent($json_array);
          if (!$target_id) {
            $target_id = $this->createAgent($json_array);
          }
          break;
        case 'SubStatement':
          $this->field_tincan_object->table = 'tincan_statement';
          $substatement_json = drupal_json_encode($json_array);
          $tincan_substatement_entity = tincan_lrs_statement_create(array('json' => $substatement_json));
          $tincan_substatement_entity->populateEntityValues();
          try {
            $save_result = $tincan_substatement_entity->save();
          }
          catch (Exception $e) {
            throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
//            services_error ('Internal Server Error', 500, 'Statement create sub-statement create save fail: ' . $e->getMessage());
          }
          if ($save_result) {
            $target_id = $tincan_substatement_entity->id;
          }
          break;
        case 'StatementRef':
          $this->field_tincan_object->table = 'statement_reference';
          if (isset($json_array['id'])) {
            $target_id = $json_array['id'];
          }
          break;
      }
    } else if(isset($json_array['object'])){
      if (isset($json_array['object']['id'])) {
        $this->field_tincan_object->id = $json_array['object']['id'];
      }

      if (isset($json_array['object']['objectType'])) {
        $this->field_tincan_object->type = $json_array['object']['objectType'];
      }

      if (isset($json_array['object']['objectType'])) {
        $this->field_tincan_object->json = Json::encode($json_array['object']);
      }

    } else {
      // We might come back to this if we want to record activities
//      $target_id = $this->findActivity($json_array);
//      if (!$target_id) {
//        $target_id = $this->createActivity($json_array);
//      }
//      $this->field_tincan_object->type = 'Activity';
//      $this->field_tincan_object->table = 'tincan_activity';
    }

    if ($target_id) {
      $this->field_tincan_object->target_id = $target_id;
    }
  }

  /**
   * Populates the tincan_result field from a decoded JSON array
   *
   * @param array $json_array
   *   Array of decoded JSON values for the result object
   */
  private function populateResult($json_array) {
    if (isset($json_array['score'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['score_json'] = drupal_json_encode($json_array['score']);

      if (isset($json_array['score']['scaled']) && is_numeric($json_array['score']['scaled'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_scaled'] = $json_array['score']['scaled'];
      }
      if (isset($json_array['score']['raw']) && is_numeric($json_array['score']['raw'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_raw'] = $json_array['score']['raw'];
      }
      if (isset($json_array['score']['min']) && is_numeric($json_array['score']['min'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_min'] = $json_array['score']['min'];
      }
      if (isset($json_array['score']['max']) && is_numeric($json_array['score']['max'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_max'] = $json_array['score']['max'];
      }
    }
    if (isset($json_array['success'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['success'] = ($json_array['success'] == 1 || $json_array['success'] == 'true') ? 1 : 0;
    }

    if (isset($json_array['completion'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['completion'] = ($json_array['completion'] == 1 || $json_array['completion'] == 'true') ? 1 : 0;
    }
    if (isset($json_array['response'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['response'] = $json_array['response'];
    }
    if (isset($json_array['duration'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['duration'] = $json_array['duration'];
    }
    if (isset($json_array['extensions'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['extensions'] = drupal_json_encode($json_array['extensions']);
    }

    $this->tincan_result[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }

}
