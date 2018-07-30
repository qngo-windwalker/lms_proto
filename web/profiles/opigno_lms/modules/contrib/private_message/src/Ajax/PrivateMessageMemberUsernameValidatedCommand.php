<?php

namespace Drupal\private_message\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class PrivateMessageMemberUsernameValidatedCommand implements CommandInterface {

  /**
   * The username that was validated
   *
   * @var string
   */
  protected $username;

  /**
   * The user information to be returned sent to the browser
   *
   * @var bool
   *   A boolean indicatating whether the given username is valid
   *   to be used as a member in a private message thread
   */
  protected $validUsername;

  /**
   * Construct a PrivateMessageMembersAutocompleteResponseCommand object
   *
   * @param string $username
   *   The username that was validated
   * @param bool $validUsername
   *   A boolean indicatating whether the given username is valid
   *   to be used as a member in a private message thread
   */
  public function __construct($username, $validUsername) {
    $this->username = $username;
    $this->validUsername = $validUsername;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'privateMessageMemberUsernameValidated',
      'username' => $this->username,
      'validUsername' => $this->validUsername,
    ];
  }
}
