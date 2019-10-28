<?php

namespace Drupal\wind_lms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;

/**
 * Provides a 'WindLMSCatalogBlock' block.
 *
 * @Block(
 *  id = "wind_lms_catalog_block",
 *  admin_label = @Translation("Catalog"),
 * )
 */
class WindLMSCatalogBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
          ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['checkbox_machine_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Test checkbox'),
      '#default_value' => $this->configuration['checkbox_machine_name'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['checkbox_machine_name'] = $form_state->getValue('checkbox_machine_name');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $title = isset($this->configuration['title']) ? $this->configuration['title'] : 'Catalog';
    $build = wind_gen_block_card_template($title);
    $rows = array();
    $groups = Group::loadMultiple();
    /** @var \Drupal\group\Entity\Group $group */
    foreach ($groups as $id => $group){
      $rows[$id] = array(
        'data' => [
          array('data' => $group->label(),  'class' => array('course-title')),
          array('data' => '',  'class' => array('course-total-license')),
          $group->label(),
        ],
        'class' => array('gid-'.$id),
      );
    }
    $header = [
      array('data' => 'Title', 'class' => 'node-title-header'),
      array('data' => 'Total Licenses', 'class' => 'node-total-licenses-header'),
      array('data' => 'Used Licenses', 'class' => 'node-used-licenses-header'),
    ];
    $tablConfig = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => 'course-tbl',
        'class' => array('table' ,'table-wind-theme-strip')
      ),
    ];
    $markup = render($tablConfig);
    $markup .= '<a class="btn btn-info" href="/course/1/adduser">View</a>';

    $build['card_body']['card_text_container']['#markup'] = $markup;

    $build['wind_lms_catalog_block_checkbox_machine_name']['#markup'] = '<p>' . $this->configuration['checkbox_machine_name'] . '</p>';

    return $build;
  }

}
