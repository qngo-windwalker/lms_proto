<?php

namespace Drupal\q_devel\Controller;

//use Drupal\block\Entity\Block;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Enity\Node;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;
//
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\group\Entity\Group;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;
use Drupal\jira_rest\JiraRestWrapperService;

class QDevelBlockController{

  public function getContent(){

    //    $configurableBlock = \Drupal\block\Entity\Block::create([
//      'id' => 'cn_theme_wind_intro_block',
//      'plugin' => 'wind_intro_block'
//    ]);
//    $configurableBlock->save();
    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
  }


  /**
   * @param string $id
   * usage:
   * disableBlock('dashboard_views_block_opigno_calendar-month_block');
   */
  public function disableBlock($id) {
    /** @var \Drupal\block\Entity\Block $block */
    $block = Block::load($id);
    if ($block) {
//      $visibility = $block->getVisibility();
//      $visibility['request_path']['pages'] = 'no-page';
//      $block->setVisibilityConfig('request_path', $visibility['request_path']);
      $block->disable()->save();
    }
  }

  public function deleteBlock($id) {
    /** @var \Drupal\block\Entity\Block $block */
    $block = Block::load($id);
    if ($block) {
      if($block->delete()){
        $block->save();
      }
    }
  }

  public function blockDev() {
//   Block::load('wind_theme_dashboard_views_block_who_s_online-who_s_online_block')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_opigno_notifications-block_unread_dashboard')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_private_message-block_dashboard')->disable()->save();
//   Block::load('wind_theme_breadcrumbs')->disable()->save();
    $block = Block::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
    //    $user_login_block = \Drupal::entityTypeManager()->getStrorage('block_content')->load('seven_login');
    //    dsm($user_login_block);
    //    $block = \Drupal\block_content\Entity\BlockContent::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);

  }

}
