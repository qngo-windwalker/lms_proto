<?php

namespace Drupal\wind\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\Utility\Html;
use Drush\Commands\DrushCommands;
use Drupal\user\Entity\User;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class WindCommands extends DrushCommands {

  /**
   * Create a new learner user and enroll to all of the training.
   *
   * @param $name
   *
   * @command wind:siteName
   * @aliases wsitename
   */
  public function siteName($name){
    $websiteName = Html::escape($name);
    $config = \Drupal::service('config.factory')->getEditable('system.site');
    $config->set('name', $websiteName);
    $config->save();
  }

  public function createUser($userInfo) {
    $user = User::create([
      'name' => $userInfo['name'],
      'pass' => $userInfo['pass'],
      'mail' => $userInfo['mail'],
      'status' => 1,
      'roles' => array('authenticated'),
    ]);
    $user->set('field_first_name', ['value' => $userInfo['first_name']]);
    $user->set('field_last_name', ['value' => $userInfo['last_name']]);
    $result = $user->save();
    if ($result) {
      $account = User::load($user->id());
      return $account;
    }
    return false;
  }

  /**
   * Command description here.
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage ch_nav-commandName foo
   *   Usage description
   *
   * @command ch_nav:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {
    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * An example of the table output format.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command ch_nav:token
   * @aliases token
   *
   * @filter-default-field name
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function token($options = ['format' => 'table']) {
    $all = \Drupal::token()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }

}
