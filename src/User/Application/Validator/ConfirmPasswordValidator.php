<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @package App\User\Application\Validator
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class ConfirmPasswordValidator extends ConstraintValidator
{
    /**
     * @param ConfirmPassword $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $password = $this->context->getRoot()->get('password')->getData();

        if ($password !== $value) {
            $this->context->buildViolation($constraint->message)
                ->atPath('password_confirmation')
                ->addViolation();
        }
    }
}
