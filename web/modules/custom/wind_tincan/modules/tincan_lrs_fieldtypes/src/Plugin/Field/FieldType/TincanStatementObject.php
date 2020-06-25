<?php

/**
 * @see https://ixis.co.uk/blog/drupal-8-creating-field-types-multiple-values
 * @file
 * Contains \Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldType\TincanStatementObject.
 */

namespace Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'dice' field type.
 *
 * @FieldType (
 *   id = "tincan_statement_object",
 *   label = @Translation("TincanStatementObject"),
 *   description = @Translation("Stores TincanStatementObject."),
 *   default_widget = "tincan_statement_object",
 *   default_formatter = "tincan_statement_object"
 * )
 */
class TincanStatementObject extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'id' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => 255,
        ),
        'type' => array(
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => 50,
        ),
        'table' => array(
          'type' => 'varchar',
          'length' => 50,
          'not null' => TRUE,
        ),
        'target_id' => array(
          'type' => 'varchar',
          'length' => 100,
          'not null' => TRUE,
        ),
        'json' => array(
          'type' => 'blob',
          'not null' => FALSE,
          'size' => 'normal',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value1 = $this->get('id')->getValue();
    $value2 = $this->get('type')->getValue();
    $value3 = $this->get('table')->getValue();
    $value4 = $this->get('json')->getValue();
    return empty($value1) && empty($value2) && empty($value3) && empty($value4);
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Add our properties.
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('id'))
      ->setDescription(t('Object Id'));

    $properties['type'] = DataDefinition::create('string')
      ->setLabel(t('type'))
      ->setDescription(t('Object type'));

    $properties['table'] = DataDefinition::create('string')
      ->setLabel(t('table'))
      ->setDescription(t('table'));

    $properties['target_id'] = DataDefinition::create('string')
      ->setLabel(t('target_id'))
      ->setDescription(t('tincan_agent.id'));

    $properties['json'] = DataDefinition::create('string')
      ->setLabel(t('json'))
      ->setDescription(t('json'))
      ->setSetting('case_sensitive', TRUE);

    return $properties;
  }
}
