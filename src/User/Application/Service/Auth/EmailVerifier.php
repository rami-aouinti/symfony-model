<?php

declare(strict_types=1);

namespace App\User\Application\Service\Auth;

use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Class EmailVerifier
 *
 * @package App\User\Application\Service\Auth
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager)
    {
    }

    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            request: $request,
            userId: (string) $user->getId(),
            userEmail: $user->getEmail()
        );

        $user->setEmailVerifiedAt(new \DateTime('now'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
