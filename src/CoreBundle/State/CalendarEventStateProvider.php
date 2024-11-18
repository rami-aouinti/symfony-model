<?php

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Access\Domain\Entity\AccessUrl;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\SessionRepository;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use App\CoreBundle\Settings\SettingsManager;
use App\Session\Domain\Entity\Session;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @template-implements ProviderInterface<CCalendarEvent|Session>
 */
final class CalendarEventStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
        private readonly Security $security,
        private readonly AccessUrlHelper $accessUrlService,
        private readonly SessionRepository $sessionRepository,
        private readonly RequestStack $requestStack,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        $accessUrl = $this->accessUrlService->getCurrent();

        /** @var array<CCalendarEvent> $cCalendarEvents */
        $cCalendarEvents = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $userSessions = [];

        $request = $this->requestStack->getMainRequest();
        $courseId = $request->query->getInt('cid');
        $sessionId = $request->query->getInt('sid');

        $inCourseBase = !empty($courseId);
        $inSession = !empty($sessionId);
        $inCourseSession = $inCourseBase && $inSession;

        $inPersonalAgenda = !$inCourseBase && !$inCourseSession;

        if (
            $inPersonalAgenda
            && $this->settingsManager->getSetting('agenda.personal_calendar_show_sessions_occupation') === 'true'
        ) {
            $userSessions = $this->getSessionList($user, $accessUrl, $context);
        }

        return array_merge($cCalendarEvents, $userSessions);
    }

    /**
     * @return array<Session>
     */
    private function getSessionList(User $user, AccessUrl $accessUrl, array $context = []): array
    {
        $qb = $this->sessionRepository->getUserFollowedSessionsInAccessUrl($user, $accessUrl);

        if (!empty($context['filters']['startDate']['before'])) {
            $qb
                ->andWhere($qb->expr()->lte('s.displayStartDate', ':value_start'))
                ->setParameter('value_start', $context['filters']['startDate']['before'])
            ;
        }

        if (!empty($context['filters']['startDate']['after'])) {
            $qb
                ->andWhere($qb->expr()->gte('s.displayStartDate', ':value_start'))
                ->setParameter('value_start', $context['filters']['startDate']['after'])
            ;
        }

        if (!empty($context['filters']['startDate']['strictly_before'])) {
            $qb
                ->andWhere($qb->expr()->lt('s.displayStartDate', ':value_start'))
                ->setParameter('value_start', $context['filters']['startDate']['strictly_before'])
            ;
        }

        if (!empty($context['filters']['startDate']['strictly_after'])) {
            $qb
                ->andWhere($qb->expr()->gt('s.displayStartDate', ':value_start'))
                ->setParameter('value_start', $context['filters']['startDate']['strictly_after'])
            ;
        }

        if (!empty($context['filters']['endDate']['before'])) {
            $qb
                ->andWhere($qb->expr()->lte('s.displayEndDate', ':value_end'))
                ->setParameter('value_end', $context['filters']['endDate']['before'])
            ;
        }

        if (!empty($context['filters']['endDate']['after'])) {
            $qb
                ->andWhere($qb->expr()->gte('s.displayEndDate', ':value_end'))
                ->setParameter('value_end', $context['filters']['endDate']['after'])
            ;
        }

        if (!empty($context['filters']['endDate']['strictly_before'])) {
            $qb
                ->andWhere($qb->expr()->lt('s.displayEndDate', ':value_end'))
                ->setParameter('value_end', $context['filters']['endDate']['strictly_before'])
            ;
        }

        if (!empty($context['filters']['endDate']['strictly_after'])) {
            $qb
                ->andWhere($qb->expr()->gt('s.displayEndDate', ':value_end'))
                ->setParameter('value_end', $context['filters']['endDate']['strictly_after'])
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
