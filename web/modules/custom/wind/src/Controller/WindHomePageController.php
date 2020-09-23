<?php

namespace Drupal\wind\Controller;

use Drupal\block\Entity\Block;

/**
 * Controller for all the actions of the Learning Path manager app.
 */
class WindHomePageController {

  public function content(){

    $nids = \Drupal::entityTypeManager()
      ->getListBuilder('node')
      ->getStorage()
      ->loadByProperties([
        'type' => 'page',
        'status' => 1,
      ]);
//    $block = Block::load('seven_login');
//    $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
//    dsm($render);

//    $user_login_block = \Drupal::entityTypeManager()->getStrorage('block_content')->load('seven_login');
//    dsm($user_login_block);
//    $block = \Drupal\block_content\Entity\BlockContent::load('seven_login');
//    $render = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);
//    $block = \Drupal\block\Entity\Block::load('seven_login');
//    $block_content = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
//    return array(
//      '#type' => 'container',
//      '#attributes' => array(),
//      '#element-content' => $block_content,
//      '#weight' => 0
//    );
//    return $render;

    return array(
      '#type' => 'markup',
      '#markup' => 'hello',
      '#prefix' => '<div id="seven-login-wrapper"><div class="row"><div class="col-md-6">',
      '#suffix' => '</div></div></div>'
    );
  }
}
