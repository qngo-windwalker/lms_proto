<?php

namespace Drupal\private_message\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class PrivateMessageInboxInsertThreadsCommand implements CommandInterface {

  /**
   * The HTML for the threads to be inserted in the page
   *
   * @var string
   */
  protected $threads;

  /**
   * Construct a PrivateMessageInboxInsertThreadsCommand object
   *
   * @param string $threads
   *   The HTML for the threads to be inserted in the page
   */
  public function __construct($threads, $timestamp) {
    $this->threads = $threads;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'insertInboxOldPrivateMessageThreads',
      'threads' => $this->threads,
    ];
  }
}
