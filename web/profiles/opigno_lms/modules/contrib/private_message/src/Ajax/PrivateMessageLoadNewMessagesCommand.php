<?php

namespace Drupal\private_message\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class PrivateMessageLoadNewMessagesCommand implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'loadNewPrivateMessages',
    ];
  }
}
