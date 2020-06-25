<?php
/**
 * @see https://ixis.co.uk/blog/drupal-8-creating-field-types-multiple-values
 *
 * @file
 * Contains \Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldWidget\TincanStatementObjectWidget.
 */
namespace Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Plugin implementation of the 'tincan_statement_object' widget.
 *
 * @FieldWidget (
 *   id = "tincan_statement_object",
 *   label = @Translation("tincan_statement_object"),
 *   field_types = {
 *     "tincan_statement_object"
 *   }
 * )
 */
class TincanStatementObjectWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element['id'] = array(
      '#type' => 'text',
      '#title' => t('ID'),
      '#default_value' => isset($items[$delta]->id) ? $items[$delta]->id : '',
      '#size' => 3,
    );
    $element['type'] = array(
      '#type' => 'text',
      '#title' => t('Type'),
      '#default_value' => isset($items[$delta]->type) ? $items[$delta]->type : '',
      '#size' => 3,
    );
    $element['table'] = array(
      '#type' => 'text',
      '#title' => t('Table'),
      '#default_value' => isset($items[$delta]->type) ? $items[$delta]->type : '',
      '#size' => 3,
    );
    $element['target_id'] = array(
      '#type' => 'text',
      '#title' => t('Sides'),
      '#default_value' => isset($items[$delta]->type) ? $items[$delta]->type : '',
      '#size' => 3,
    );
    $element['json'] = array(
      '#type' => 'text',
      '#title' => t('Modifier'),
      '#default_value' => isset($items[$delta]->json) ? $items[$delta]->json : '',
      '#size' => 3,
    );
    // If cardinality is 1, ensure a label is output for the field by wrapping
    // it in a details element.
    if ($this->fieldDefinition->getFieldStorageDefinition()->getCardinality() == 1) {
      $element += array(
        '#type' => 'fieldset',
        '#attributes' => array('class' => array('container-inline')),
      );
    }
    return $element;
  }
}
