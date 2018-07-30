<?php

namespace Drupal\private_message\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the any submitted members for the thread have permission to use
 * the private message system.
 *
 * @Constraint(
 *   id = "private_message_thread_member",
 *   label = @Translation("Private Message Thread Member", context = "Validation"),
 * )
 */
class PrivateMessageThreadMemberConstraint extends Constraint {
  // The message that is shown if the user does not have permission to use the private
  // message system. We give a user not found error rather than a permissions error,
  // as revealing a permissions error would allow users to discover users on the site
  // who are not part of the private message system.
  public $userPrivateMessagePermissionError = 'User %user not found';
}
