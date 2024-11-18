<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Access\Domain\Entity\AccessUrl;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Entity\User\Usergroup;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\SessionRepository;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use App\CoreBundle\ServiceHelper\CidReqHelper;
use App\CoreBundle\ServiceHelper\UserHelper;
use App\CoreBundle\Settings\SettingsManager;
use Doctrine\ORM\QueryBuilder;
use UserGroupModel;

/**
 * @package App\CoreBundle\DataProvider\Extension
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CCalendarEventExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly CidReqHelper $cidReqHelper,
        private readonly UserHelper $userHelper,
        private readonly AccessUrlHelper $accessUrlHelper,
        private readonly SettingsManager $settingsManager,
        private readonly CourseRepository $courseRepository,
        private readonly SessionRepository $sessionRepository,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass, $context);
    }

    private function addWhere(QueryBuilder $qb, string $resourceClass, array $context): void
    {
        if ($resourceClass !== CCalendarEvent::class) {
            return;
        }

        $isGlobalType = isset($context['filters']['type']) && $context['filters']['type'] === 'global';
        if ($isGlobalType) {
            return;
        }

        $courseId = $this->cidReqHelper->getCourseId();
        $sessionId = $this->cidReqHelper->getSessionId();
        $groupId = $this->cidReqHelper->getGroupId();
        $user = $this->userHelper->getCurrent();
        $accessUrl = $this->accessUrlHelper->getCurrent();

        $inCourseBase = !empty($courseId);
        $inSession = !empty($sessionId);
        $inCourseSession = $inCourseBase && $inSession;

        $inPersonalList = !$inCourseBase && !$inCourseSession;

        $alias = $qb->getRootAliases()[0];

        $qb
            ->innerJoin("{$alias}.resourceNode", 'node')
            ->leftJoin('node.resourceLinks', 'resource_links')
        ;

        if ($inPersonalList && $user) {
            $this->addPersonalCalendarConditions($qb, $user);
            $this->addCourseConditions($qb, $user, $accessUrl);
            $this->addSessionConditions($qb, $user, $accessUrl);
        }
    }

    private function addPersonalCalendarConditions(QueryBuilder $qb, User $user): void
    {
        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('resource_links.user', ':user'),
                    $qb->expr()->eq('node.creator', ':user')
                )
            )
            ->setParameter('user', $user->getId())
        ;

        $this->addSubscriptionsConditions($qb, $user);
    }

    private function addSubscriptionsConditions(QueryBuilder $qb, User $user): void
    {
        $groupList = (new UserGroupModel())->getUserGroupListByUser($user->getId(), Usergroup::NORMAL_CLASS);
        $groupIdList = $groupList ? array_column($groupList, 'id') : [];

        $alias = $qb->getRootAliases()[0];

        $expr = $qb->expr()->orX(
            $qb->expr()->eq("{$alias}.subscriptionVisibility", ':visibility_all'),
        );

        if ($groupIdList) {
            $expr->add(
                $qb->expr()->orX(
                    $qb->expr()->eq("{$alias}.subscriptionVisibility", ':visibility_class'),
                    $qb->expr()->in("{$alias}.subscriptionItemId", ':item_id_list')
                )
            );

            $qb->setParameter('visibility_class', CCalendarEvent::SUBSCRIPTION_VISIBILITY_CLASS);
            $qb->setParameter('item_id_list', $groupIdList);
        }

        $qb
            ->orWhere($expr)
            ->setParameter(':visibility_all', CCalendarEvent::SUBSCRIPTION_VISIBILITY_ALL)
        ;
    }

    private function addCourseConditions(QueryBuilder $qb, User $user, AccessUrl $accessUrl): void
    {
        $courseSubscriptions = $this->courseRepository->getCoursesByUser($user, $accessUrl);

        $courseIdList = [];

        foreach ($courseSubscriptions as $courseSubscription) {
            $courseIdList[] = $courseSubscription->getCourse()->getId();
        }

        if ($courseIdList) {
            $qb
                ->orWhere(
                    $qb->expr()->andX(
                        $qb->expr()->in('resource_links.course', ':course_id_list'),
                        $qb->expr()->isNull('resource_links.session')
                    )
                )
                ->setParameter('course_id_list', $courseIdList)
            ;
        }
    }

    private function addSessionConditions(QueryBuilder $qb, User $user, AccessUrl $accessUrl): void
    {
        $sessionIdList = [];
        $courseIdList = [];

        if (
            $user->isHRM()
            && $this->settingsManager->getSetting('session.drh_can_access_all_session_content') === 'true'
        ) {
            $sessions = $this->sessionRepository
                ->getUserFollowedSessionsInAccessUrl($user, $accessUrl)
                ->getQuery()
                ->getResult()
            ;

            foreach ($sessions as $session) {
                foreach ($session->getCourses() as $sessionRelCourse) {
                    $courseIdList[] = $sessionRelCourse->getCourse()->getId();
                }

                $sessionIdList[] = $session->getId();
            }
        } else {
            $sessions = $this->sessionRepository->getSessionsByUser($user, $accessUrl)->getQuery()->getResult();

            foreach ($sessions as $session) {
                foreach ($session->getSessionRelCourseByUser($user) as $sessionRelCourse) {
                    $courseIdList[] = $sessionRelCourse->getCourse()->getId();
                }

                $sessionIdList[] = $session->getId();
            }
        }

        if ($sessionIdList && $courseIdList) {
            $qb
                ->orWhere(
                    $qb->expr()->andX(
                        $qb->expr()->in('resource_links.session', ':session_id_list'),
                        $qb->expr()->in('resource_links.course', ':course_id_list')
                    )
                )
                ->setParameter('session_id_list', array_unique($sessionIdList))
                ->setParameter('course_id_list', $courseIdList)
            ;
        }
    }
}
