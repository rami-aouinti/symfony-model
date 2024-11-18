<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\EventSubscriber;

use App\CoreBundle\Repository\TrackELoginRecordRepository;
use App\CoreBundle\ServiceHelper\LoginAttemptLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TrackELoginRecordRepository $trackELoginRecordingRepository,
        private readonly RequestStack $requestStack,
        private readonly LoginAttemptLogger $loginAttemptLogger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => ['onFailureEvent', 10],
        ];
    }

    public function onFailureEvent(LoginFailureEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $userIp = $request ? $request->getClientIp() : 'unknown';

        $passport = $event->getPassport();

        /** @var UserBadge $userBadge */
        $userBadge = $passport->getBadge(UserBadge::class);
        $username = $userBadge->getUserIdentifier();

        // Log of connection attempts
        $this->trackELoginRecordingRepository->addTrackLogin($username, $userIp, false);
        $this->loginAttemptLogger->logAttempt(false, $username, $userIp);
    }
}
