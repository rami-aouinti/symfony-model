<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Auth;

use App\User\Application\Service\Auth\EmailVerifier;
use App\User\Domain\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

/**
 * @package App\Controller\Auth
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class VerificationController extends AbstractController implements AuthController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier
    ) {
    }

    #[Route('/email/verify', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('user_property');
        }

        $this->addFlash('success', 'message.email_verified');

        return $this->redirectToRoute('user_property');
    }
}
