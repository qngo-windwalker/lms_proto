<?php

namespace Drupal\private_message\Ajax;

class PrivateMessageInsertNewMessagesCommand extends PrivateMessageInsertMessagesCommand {

  /**
   * Construct a PrivateMessageInsertNewMessagesCommand object
   *
   * @param string $messages
   *   The HTML for the messages to be inserted in the page
   */
  public function __construct($messages) {
    parent::__construct('new', $messages);
  }
}
