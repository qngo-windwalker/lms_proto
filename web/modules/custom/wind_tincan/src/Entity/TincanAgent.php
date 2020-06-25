<?php

namespace Drupal\wind_tincan\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines the tincan_agent entity.
 *
 * @ingroup tincan_agent
 *
 * @ContentEntityType(
 *   id = "tincan_agent",
 *   label = @Translation("Tincan Agent"),
 *   base_table = "tincan_agent",
 *   entity_keys = {
 *     "id" = "id",
 *     "object_type" = "object_type",
 *   },
 *   config_export = {
 *     "id",
 *     "object_type",
 *   }
 * )
 */
class TincanAgent extends ContentEntityBase implements ContentEntityInterface {

  private $notation;

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
    $fields['object_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('object_type'))
      ->setDescription(t('Agent Object Type (either Agent or Group)'))
      ->setSettings(array(
        'max_length' => 10,
        'text_processing' => 0,
      ))
      ->setReadOnly(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('name'))
      ->setDescription(t('Full name of the Agent.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['mbox'] = BaseFieldDefinition::create('string')
      ->setLabel(t('mbox'))
      ->setDescription(t('mailto IRI of the Agent.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['mbox_sha1sum'] = BaseFieldDefinition::create('string')
      ->setLabel(t('mbox_sha1sum'))
      ->setDescription(t('The SHA1 hash of a mailto IRI.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['openid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('openid'))
      ->setDescription(t('An openID that uniquely identifies the Agent.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['account_home_page'] = BaseFieldDefinition::create('string')
      ->setLabel(t('account_home_page'))
      ->setDescription(t('The canonical home page for the system the account is on.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['account_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('account_name'))
      ->setDescription(t('The unique id or name used to log in to this account. Could be Drupal UID'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

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
    /** @var \Drupal\wind_tincan\Entity\TincanAgent $tincan_agent */
    $tincan_agent = parent::create($values);
    if(isset($values['json'])){
      $tincan_agent->notation = $values['json'];
      $tincan_agent->populateEntityValues();
    }
    return $tincan_agent;
  }
  /**
   * Sets the working JSON for the entity
   */
  public function setJSON($json) {
    $this->notation = $json;
  }

  /**
   * Validates the JSON for the entity
   *
   * @return Boolean
   *   Returns TRUE if the JSON validates, otherwise FALSE
   */
  public function validateJSON() {
    if($this->notation == '') {
      return FALSE;
    }
    return _wind_tincan_json_validation($this->notation,'tincan_agent entity validation');
  }

  /**
   * Populates the properties and fields for the entity from the decoded entity JSON
   */
  public function populateEntityValues() {
    if($this->notation == '') return FALSE;
    if($this->validateJSON()) {
      //process and populate entity
      $this->populate();
    }
  }


  /**
   * Populates the entity properties and fields from set entity JSON
   */
  private function populate() {
    $json = $this->notation;
    $json_array = Json::decode($json);

    // objectType
    if (isset($json_array['objectType'])) {
      $this->object_type = $json_array['objectType'];
    } else {
      $this->object_type = 'Agent';
    }
    // name
    if (isset($json_array['name'])) {
      $this->name = $json_array['name'];
    } else if (isset($json_array['actor']) && isset($json_array['actor']['name'])) {
      $this->name = $json_array['actor']['name'];
    }
    $ifi_found = 0;
    // mbox
    if (isset($json_array['mbox'])) {
      $this->mbox= $json_array['mbox'];
      $ifi_found = 1;
    }
    // mbox_sha1sum
    if (isset($json_array['mbox_sha1sum']) && !$ifi_found) {
      $this->mbox_sha1sum = $json_array['mbox_sha1sum'];
      $ifi_found = 1;
    }
    // openid
    if (isset($json_array['openid']) && !$ifi_found) {
      $this->openid= $json_array['openid'];
      $ifi_found = 1;
    }
    // account
    // @see https://github.com/adlnet/xAPI-Spec/blob/master/xAPI-Data.md#actor
    if (isset($json_array['account'])) {
      if (isset($json_array['account']['homePage']) && isset($json_array['account']['name']) && !$ifi_found) {
        // homepage
        $this->account_home_page = $json_array['account']['homePage'];
        // account name
        $this->account_name = $json_array['account']['name'];
        $ifi_found = 1;
      }
    }
    // actor
    if (isset($json_array['actor'])) {
      $json_array_actor = $json_array['actor'];
      if (isset($json_array_actor['account']) && isset($json_array_actor['account']['homePage']) && isset($json_array_actor['account']['name']) && !$ifi_found) {
        // homepage
        $this->account_home_page = $json_array_actor['account']['homePage'];
        // account name
        $this->account_name = $json_array_actor['account']['name'];
        $ifi_found = 1;
      }
    }

    //members
    // TODO
//    if (isset($json_array['member'])) {
//      if (!isset($json_array['member'][0]) ) {
//        $target_id = $this->findMember($json_array['member']);
//        if (!$target_id) {
//          $target_id = $this->createMember($json_array['member']);
//        }
//        if ($target_id) {
//          $this->tincan_group_members[LANGUAGE_NONE][0]['target_id'] = $target_id;
//        }
//      }
//      else {
//        $count  = 0;
//        foreach ($json_array['member'] as $item) {
//          $target_id = $this->findMember($item);
//          if (!$target_id) {
//            $target_id = $this->createMember($item);
//          }
//          if ($target_id) {
//            $this->tincan_group_members[LANGUAGE_NONE][$count]['target_id'] = $target_id;
//          }
//          $count += 1;
//        }
//      }
//    } //end if isset members
  } //end populate method

  /**
   * Finds an agent entity
   *
   * @param array $json_array
   *   Array of values parsed from JSON
   *
   * @return integer
   *   Returns the entity id of the agent if found, otherwise 0;
   */
  private function findMember($json_array) {
    if (!isset($json_array['mbox']) &&
      !isset($json_array['mbox_sha1sum']) &&
      !isset($json_array['openid']) &&
      (!isset($json_array['account']) && !isset($json_array['account']['homePage']) && ! isset($json_array['account']['name'])) ) {
      return 0;
    }

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type','tincan_agent');
    $query->propertyCondition('object_type','Agent');

    $ifi_found = 0;

    if (isset($json_array['mbox'])) {
      $query->propertyCondition('mbox',$json_array['mbox']);
      $ifi_found = 1;
    }
    if (isset($json_array['mbox_sha1sum']) && !$ifi_found) {
      $query->propertyCondition('mbox_sha1sum',$json_array['mbox_sha1sum']);
      $ifi_found = 1;
    }
    if (isset($json_array['openid']) && !$ifi_found) {
      $query->propertyCondition('openid',$json_array['openid']);
      $ifi_found = 1;
    }
    if (isset($json_array['account']) && isset($json_array['account']['homePage']) && isset($json_array['account']['name']) && !$ifi_found) {
      $query->propertyCondition('account_home_page',$json_array['account']['homePage']);
      $query->propertyCondition('account_name',$json_array['account']['name']);
      $ifi_found = 1;
    }
    $result = $query->execute();

    if (isset($result['tincan_agent'])) {
      foreach ($result['tincan_agent'] as $key => $agent) {
        return $key;
      }
    }
    else return 0;
  }

}
