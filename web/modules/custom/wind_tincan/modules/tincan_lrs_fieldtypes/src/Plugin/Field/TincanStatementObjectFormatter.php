<?php
/**
 * @see https://ixis.co.uk/blog/drupal-8-creating-field-types-multiple-values
 * @file
 * Contains \Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldFormatter\TincanStatementObjectFormatter.
 */
namespace Drupal\tincan_lrs_fieldtypes\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
/**
 * Plugin implementation of the 'tincan_statement_object' formatter.
 *
 * @FieldFormatter (
 *   id = "tincan_statement_object",
 *   label = @Translation("tincan_statement_object"),
 *   field_types = {
 *     "dice"
 *   }
 * )
 */
class TincanStatementObjectFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = array();
    foreach ($items as $delta => $item) {
      // @see https://ixis.co.uk/blog/drupal-8-creating-field-types-multiple-values
      $markup = '';
      $elements[$delta] = array(
        '#type' => 'markup',
        '#markup' => $markup,
      );
    }
    return $elements;
  }
}
