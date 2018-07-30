<?php

namespace Drupal\private_message\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PrivateMessageThreadMemberConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $users = $items->referencedEntities();
    foreach ($users as $user) {
      if(!$user->hasPermission('use private messaging system')) {
        $this->context->addViolation($constraint->userPrivateMessagePermissionError, ['%user' => $user->getDisplayName()]);
      }
    }
  }
}
