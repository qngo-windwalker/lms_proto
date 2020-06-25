<?php

namespace Drupal\wind_tincan\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Defines the tincan_state entity.
 *
 * @ingroup tincan_state
 *
 * @ContentEntityType(
 *   id = "tincan_state",
 *   label = @Translation("Tincan State"),
 *   base_table = "tincan_state",
 *   entity_keys = {
 *     "id" = "id",
 *     "state_id" = "state_id",
 *     "activity_id" = "activity_id",
 *   },
 *   config_export = {
 *     "id",
 *     "statement_id",
 *   }
 * )
 */
class TincanState extends ContentEntityBase implements ContentEntityInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the TincanStatement entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['state_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('state_id'))
      ->setDescription(t('State unique id.'))
      ->setSettings(array(
        'max_length' => 64,
        'text_processing' => 0,
      ))
      ->setReadOnly(TRUE);

    $fields['activity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('state_id'))
      ->setDescription(t('Activity id providing context for this state.'))
      ->setSettings(array(
        'max_length' => 255,
        'text_processing' => 0,
      ))
      ->setReadOnly(TRUE);

    $fields['registration'] = BaseFieldDefinition::create('string')
      ->setLabel(t('registration'))
      ->setDescription(t('Registration UUID.'))
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setReadOnly(TRUE);

    $fields['stored_date'] = BaseFieldDefinition::create('created')
      ->setLabel(t('stored_date'))
      ->setDescription(t('The Unix timestamp when the invite was created.'));

    $fields['updated'] = BaseFieldDefinition::create('created')
      ->setLabel(t('updated'))
      ->setDescription(t('The Unix timestamp when the events described within the statement occurred.'));

    // Standard field, unique outside of the scope of the current project.
    $fields['content_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('content_type'))
      ->setDescription(t('HTTP Content type of the document.'))
      ->setSettings(array(
        'max_length' => 64,
      ))
      ->setReadOnly(TRUE);

    // @see https://www.drupal.org/docs/8/api/entity-api/fieldtypes-fieldwidgets-and-fieldformatters
    // @see example: https://www.drupal8.ovh/en/tutoriels/263/custom-content-entity-field-types-of-drupal-8
    // Todo: Code below will create longblob (4GB). If possible, reduce it to 'blob:normal' => 'BLOB'. @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Database%21Driver%21mysql%21Schema.php/function/Schema%3A%3AgetFieldTypeMap/8.1.x
    $fields['contents'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('contents'))
      ->setSetting('case_sensitive', TRUE)
      ->setDescription(t('The contents of the state document.'));

    return $fields;
  }

  public function findAgent($json) {
    $json_array = Json::decode($json);
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
    return array_shift($result);
  }

  /**
   * Creates an agent entity
   *
   * @param string $json
   *   String with a JSON Agent Object
   *
   * @return integer
   *   Returns the entity id of the agent if created successfully, otherwise 0;
   */
  function createAgent($json) {
    $values = array();
    $target_id = 0;
    $values['json'] = $json;

    $tincan_agent_entity = TincanAgent::create($values);

    try {
      $save_result = $tincan_agent_entity->save();
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
      //      services_error ('Internal Server Error', 500, 'Statement save fail: ' . $e->getMessage());
    }

    if ($save_result) {
      $target_id = $tincan_agent_entity->id();
    }

    return $target_id;
  }
}
