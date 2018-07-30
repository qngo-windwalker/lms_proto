<?php

namespace Drupal\private_message\Controller;

interface PrivateMessageControllerInterface {

  /**
   * The Private message page on which users will be able to create, view, and reply
   * to private messages.
   */
  public function privateMessagePage();

  /**
   * The page on which settings specific to private message entities can be adjusted
   */
  public function pmSettingsPage();

  /**
   * The page on which settings specific to private message threads can be adjusted
   */
  public function pmThreadSettingsPage();

  /**
   * The page on which preparation of the uninstallation of the private message module
   * can be executed.
   */
  public function adminUninstallPage();
}
