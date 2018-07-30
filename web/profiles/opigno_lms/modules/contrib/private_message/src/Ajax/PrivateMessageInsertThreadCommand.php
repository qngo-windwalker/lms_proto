<?php

namespace Drupal\private_message\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class PrivateMessageInsertThreadCommand implements CommandInterface {

  /**
   * The HTML of the thread to be inserted
   *
   * @var string
   */
  protected $thread;

  /**
   * Construct a PrivateMessageInsertThreadCommand object
   *
   * @param string $thread
   *   The HTML of the thread to be inserted
   */
  public function __construct($thread) {
    $this->thread = $thread;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'privateMessageInsertThread',
      'thread' => $this->thread,
    ];
  }
}
