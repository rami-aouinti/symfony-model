<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CoreBundle\Repository\SessionRepository;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use App\CoreBundle\ServiceHelper\UserHelper;
use App\Session\Domain\Entity\Session;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @template-implements ProviderInterface<Session>
 */
readonly class UserSessionSubscriptionsStateProvider implements ProviderInterface
{
    public function __construct(
        private UserHelper $userHelper,
        private AccessUrlHelper $accessUrlHelper,
        private UserRepository $userRepository,
        private SessionRepository $sessionRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->userRepository->find($uriVariables['id']);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $currentUser = $this->userHelper->getCurrent();
        $url = $this->accessUrlHelper->getCurrent();

        $isAllowed = $user === $currentUser || $currentUser->isAdmin();

        if (!$isAllowed) {
            throw new AccessDeniedException();
        }

        return match ($operation->getName()) {
            'user_session_subscriptions_past' => $this->sessionRepository->getPastSessionsOfUserInUrl($user, $url),
            'user_session_subscriptions_current' => $this->sessionRepository->getCurrentSessionsOfUserInUrl($user, $url),
            'user_session_subscriptions_upcoming' => $this->sessionRepository->getUpcomingSessionsOfUserInUrl($user, $url),
        };
    }
}
