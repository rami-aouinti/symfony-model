<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\Api;

use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Entity\AgendaReminder;
use App\CoreBundle\Settings\SettingsManager;
use App\CourseBundle\Repository\CCalendarEventRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UpdateCCalendarEventAction
 *
 * @package App\CoreBundle\Controller\Api
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class UpdateCCalendarEventAction extends BaseResourceFileAction
{
    /**
     * @throws Exception
     */
    public function __invoke(
        CCalendarEvent $calendarEvent,
        Request $request,
        CCalendarEventRepository $repo,
        EntityManager $em,
        SettingsManager $settingsManager,
    ): CCalendarEvent {
        $this->handleUpdateRequest($calendarEvent, $repo, $request, $em);

        $result = json_decode($request->getContent(), true);

        $calendarEvent
            ->setContent($result['content'] ?? '')
            ->setComment($result['comment'] ?? '')
            ->setColor($result['color'] ?? '')
            ->setStartDate(new DateTime($result['startDate'] ?? ''))
            ->setEndDate(new DateTime($result['endDate'] ?? ''))
            // ->setAllDay($result['allDay'] ?? false)
            ->setCollective($result['collective'] ?? false)
        ;

        $calendarEvent->getReminders()->clear();

        if (isset($result['reminders'])) {
            foreach ($result['reminders'] as $reminderInfo) {
                $reminder = new AgendaReminder();
                $reminder->count = $reminderInfo['count'];
                $reminder->period = $reminderInfo['period'];
                $reminder->decodeDateInterval();

                $calendarEvent->addReminder($reminder);
            }
        }

        return $calendarEvent;
    }
}
