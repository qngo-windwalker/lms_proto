<?php

namespace Drupal\private_message\Ajax;

class PrivateMessageInsertPreviousMessagesCommand extends PrivateMessageInsertMessagesCommand {

  /**
   * Construct a PrivateMessageInsertPreviousMessagesCommand object
   *
   * @param string $messages
   *   The HTML for the messages to be inserted in the page
   */
  public function __construct($messages) {
    parent::__construct('previous', $messages);
  }
}
