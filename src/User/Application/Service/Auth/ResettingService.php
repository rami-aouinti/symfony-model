<?php

declare(strict_types=1);

namespace App\User\Application\Service\Auth;

use App\Platform\Application\Utils\TokenGenerator;
use App\Property\Application\Service\AbstractService;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\ResettingRepository;
use App\User\Transport\Message\SendResetPasswordLink;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class ResettingService
 *
 * @package App\User\Application\Service\Auth
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class ResettingService extends AbstractService
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        private readonly ResettingRepository $repository,
        private readonly MessageBusInterface $messageBus,
        private readonly TokenGenerator $generator
    ) {
        parent::__construct($tokenManager, $requestStack);
    }

    /**
     * @throws RandomException
     * @throws ExceptionInterface
     */
    public function sendResetPasswordLink(Request $request): void
    {
        /** @var User $user */
        $user = $this->repository->findOneBy(['email' => $request->get('user_email')['email']]);

        if (!$user->isPasswordRequestNonExpired($user::RETRY_TTL)) {
            $this->updateToken($user);
            $this->messageBus->dispatch(new SendResetPasswordLink($user));
            $this->addFlash('success', 'message.emailed_reset_link');
        }
    }

    /**
     * Generating a Confirmation Token.
     *
     * @throws RandomException
     * @return string
     */
    private function generateToken(): string
    {
        return $this->generator->generateToken();
    }

    /**
     * Refreshing a Confirmation Token.
     *
     * @param User $user
     *
     * @throws RandomException
     */
    private function updateToken(User $user): void
    {
        $this->repository->setToken($user, $this->generateToken());
    }
}
