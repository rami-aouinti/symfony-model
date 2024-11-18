<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @package App\User\Application\Validator
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class RegisteredUserValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @param RegisteredUser $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $existingUser = $this->userRepository->findOneBy([
            'email' => $value,
        ]);

        if (!$existingUser instanceof User) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
