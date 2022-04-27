<?php

namespace Drupal\wind_notify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\wind_tincan\Entity\TincanState;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\wind_notify\WindNotifyUserService;

class  WindNotifyPreview extends ControllerBase{

  /**
   * route: wind_tincan.admin.tincan:
   *  path: '/admin/tincan'
   * @param string $uid
   */
  public function getContent() {
    $opt = \Drupal::request()->get('opt');
    $rows = $this->getTableRows($opt);
    $previewHref = '/admin/wind-notify/preview';
    $markup = "
<div id=\"block-claro-local-actions\">
  <ul class=\"local-actions\">
    {$this->genPreviewListItem(WindNotifyUserService::USER_THEE_DAYS_CHECK_IN_ID, '3 Days Check In')}
    {$this->genPreviewListItem(WindNotifyUserService::USER_ONE_WEEK_CHECK_IN_ID, '1 Week Check In')}
    {$this->genPreviewListItem(WindNotifyUserService::USER_TWO_WEEKS_CHECK_IN_ID, '2 Weeks Check In')}
    {$this->genPreviewListItem(WindNotifyUserService::USER_NEVER_CHECK_IN, 'Never Check In')}
    </ul>
</div>";

//    $log = '<pre>' . print_r($rows, TRUE) . '</pre>';
//    \Drupal::logger('skywind')->notice($log);

    return [
      'options' => array(
        '#markup' => $markup,
      ),
      'table' => array(
        '#type' => 'table',
        '#header' => array('Id', 'Username', 'Mail', 'Created', 'Access', 'Login', 'Changed', 'Operations'),
        '#rows' => $rows,
        '#empty' => t('There are no data available.'),
      ),
    ];
  }

  public function getUserContent(\Drupal\user\UserInterface $user) {
    return [
      'mail1' => array(
        '#markup' => '<iframe>' . WindNotifyUserService::getNotifyBody($user, WindNotifyUserService::USER_ONE_WEEK_CHECK_IN_ID) . '</iframe>',
      ),
    ];
  }

  private function getTableRows($opt){
    $rows = [];
    switch ($opt){
      case 'three-days-check':
        $results = WindNotifyUserService::getAllUsersThreeDaysCheckIn();
        break;
      case WindNotifyUserService::USER_ONE_WEEK_CHECK_IN_ID:
        $results = WindNotifyUserService::getAllUsersOneWeekCheckIn();
        break;
      case WindNotifyUserService::USER_TWO_WEEKS_CHECK_IN_ID:
        $results = WindNotifyUserService::getAllUsersTwoWeeksCheckIn();
        break;
      case WindNotifyUserService::USER_NEVER_CHECK_IN:
        $results = WindNotifyUserService::getAllUsersNeverCheckIn();
        break;
      default:
        return [];
    }
    $users = User::loadMultiple($results);
    foreach ($users as $user) {
      $rows[] = [
        new FormattableMarkup('<a href="/user/@codes">@codes</a>', ['@codes' => $user->id()]),
        $user->getAccountName(),
        $user->getEmail(),
        $this->getDateFormated($user->getCreatedTime()),
        $this->getDateFormated( $user->get('access')->getString() ),
        $this->getDateFormated( $user->getLastLoginTime() ),
//        $this->getDateFormated( $user->get('login')->getString() ), // This works too
        $this->getDateFormated( $user->get('changed')->getString() ),
        array(
          'data' => new FormattableMarkup( $this->getOperations($user), []),
          'class' => 'views-field views-field-operations'
        )
      ];
    }
    return $rows;
  }

  private function getFileInfoTableElement(File $file, User $user, $tincan) {
    $currentUri = \Drupal::request()->getRequestUri();
    $rows = [];
    $rows[] = [
      $file->id(),
      new FormattableMarkup("<a href='/admin/tincan/user/{$user->id()}/file/{$file->id()}?destination={$currentUri}'>{$file->label()}</a>", []),
      $tincan->activity_id,
      new FormattableMarkup("<a href='/admin/tincan/user/@uid/file/{$file->id()}/delete-all-statements?destination={$currentUri}' class='action-link action-link--danger action-link--icon-trash'>Delete All Statements</a>", ['@uid' => $user->id()]),
    ];
    return array(
      '#type' => 'table',
      '#header' => array('fid', 'filename', 'tincan activity_id', 'Operations'),
      '#rows' => $rows,
      '#empty' => t('There are no data available.'),
    );
  }

  private function getDateFormated($timestamp) {
    $humanReadable = date('D d M Y', $timestamp);
    $today = time();
    $difference = $today - $timestamp;
    $count = floor($difference / 86400);  // (60 * 60 * 24)
    $count .= ' day(s) ago';  // (60 * 60 * 24)
    if ($timestamp == 0) {
      $humanReadable = 'Never';
      $count = '';
    }
    return $timestamp . ' -- ' . $humanReadable . ' -- ' . $count;
  }

  private function getOperations(User $user) {
    return '
<div>
    <a href="/admin/wind-notify/user/' . $user->id() . '/preview" hreflang="en">Preview Mail</a>
</div>';
  }

  private function genPreviewListItem(string $field_notification_id, $label) {
    return "<li class=\"local-actions__item\">
        <a href='/admin/wind-notify/preview?opt={$field_notification_id}' class='button button--action button--primary'>$label</a>
    </li>";
  }


}
