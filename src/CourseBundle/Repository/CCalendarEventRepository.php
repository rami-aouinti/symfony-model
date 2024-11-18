<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\Announcement\Domain\Entity\CAnnouncement;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Entity\AgendaReminder;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\Group\CGroup;
use App\Platform\Domain\Entity\AbstractResource;
use App\Session\Domain\Entity\Session;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;

final class CCalendarEventRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCalendarEvent::class);
    }

    public function createFromAnnouncement(
        CAnnouncement $announcement,
        DateTime $startDate,
        DateTime $endDate,
        array $users,
        Course $course,
        ?Session $session = null,
        ?CGroup $group = null,
        array $remindersInfo = [],
    ): CCalendarEvent {
        $event = (new CCalendarEvent())
            ->setTitle($announcement->getTitle())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setContent($announcement->getContent())
            ->setParent($course)
            ->setCreator($announcement->getCreator())
        ;

        $em = $this->getEntityManager();

        if (empty($users) || (isset($users[0]) && $users[0] === 'everyone')) {
            $event->addCourseLink($course, $session, $group);
        } else {
            $sendTo = AbstractResource::separateUsersGroups($users);

            if (\is_array($sendTo['groups']) && !empty($sendTo['groups'])) {
                $sendTo['groups'] = array_map(
                    fn ($groupId) => $em->find(CGroup::class, $groupId),
                    $sendTo['groups']
                );
                $sendTo['groups'] = array_filter($sendTo['groups']);

                $event->addResourceToGroupList($sendTo['groups'], $course, $session);
            }

            // Storing the selected users.
            if (\is_array($sendTo['users'])) {
                $sendTo['users'] = array_map(
                    fn ($userId) => $em->find(User::class, $userId),
                    $sendTo['users']
                );
                $sendTo['users'] = array_filter($sendTo['users']);

                $event->addResourceToUserList($sendTo['users'], $course, $session, $group);
            }
        }

        foreach ($remindersInfo as $reminderInfo) {
            $reminder = new AgendaReminder();
            $reminder->count = (int)$reminderInfo[0];
            $reminder->period = $reminderInfo[1];

            $reminder->decodeDateInterval();

            $event->addReminder($reminder);
        }

        $em->persist($event);
        $em->flush();

        return $event;
    }
}
