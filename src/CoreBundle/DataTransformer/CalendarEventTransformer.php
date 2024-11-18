<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\ApiResource\CalendarEvent;
use App\CoreBundle\Entity\AgendaReminder;
use App\CoreBundle\Repository\Node\UsergroupRepository;
use App\CoreBundle\Settings\SettingsManager;
use App\CourseBundle\Repository\CCalendarEventRepository;
use App\Session\Domain\Entity\Session;
use App\Session\Domain\Entity\SessionRelCourse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CalendarEventTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly UsergroupRepository $usergroupRepository,
        private readonly CCalendarEventRepository $calendarEventRepository,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function transform($object, string $to, array $context = []): object
    {
        if ($object instanceof Session) {
            return $this->mapSessionToDto($object);
        }

        return $this->mapCCalendarToDto($object);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return ($data instanceof CCalendarEvent || $data instanceof Session) && $to === CalendarEvent::class;
    }

    private function mapCCalendarToDto(object $object): CalendarEvent
    {
        \assert($object instanceof CCalendarEvent);

        $object->setResourceLinkListFromEntity();

        $subscriptionItemTitle = null;

        if ($object->getSubscriptionVisibility() == CCalendarEvent::SUBSCRIPTION_VISIBILITY_CLASS) {
            $subscriptionItemTitle = $this->usergroupRepository->find($object->getSubscriptionItemId())?->getTitle();
        }

        $eventType = $object->determineType();
        $color = $this->determineEventColor($eventType);

        $calendarEvent = new CalendarEvent(
            'calendar_event_' . $object->getIid(),
            $object->getTitle(),
            $object->getContent(),
            $object->getStartDate(),
            $object->getEndDate(),
            $object->isAllDay(),
            null,
            $object->getInvitationType(),
            $object->isCollective(),
            $object->getSubscriptionVisibility(),
            $object->getSubscriptionItemId(),
            $subscriptionItemTitle,
            $object->getMaxAttendees(),
            null,
            $object->getResourceNode(),
            $object->getResourceLinkListFromEntity(),
            $color
        );

        $calendarEvent->setType($eventType);

        $object->getReminders()->forAll(fn (int $i, AgendaReminder $reminder) => $reminder->encodeDateInterval());

        $calendarEvent->reminders = $object->getReminders();

        return $calendarEvent;
    }

    private function mapSessionToDto(object $object): CalendarEvent
    {
        \assert($object instanceof Session);

        /** @var ?SessionRelCourse $sessionRelCourse */
        $sessionRelCourse = $object->getCourses()->first();
        $course = $sessionRelCourse?->getCourse();

        $sessionUrl = null;

        if ($course) {
            $baseUrl = $this->router->generate('index', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $sessionUrl = "{$baseUrl}course/{$course->getId()}/home?" . http_build_query([
                'sid' => $object->getId(),
            ]);
        }

        return new CalendarEvent(
            'session_' . $object->getId(),
            $object->getTitle(),
            $object->getShowDescription() ? $object->getDescription() : null,
            $object->getDisplayStartDate(),
            $object->getDisplayEndDate(),
            false,
            $sessionUrl,
        );
    }

    private function determineEventColor(string $eventType): string
    {
        $defaultColors = [
            'platform' => 'red',
            'course' => '#458B00',
            'session' => '#00496D',
            'personal' => 'steel blue',
        ];

        $agendaColors = [];
        $settingAgendaColors = $this->settingsManager->getSetting('agenda.agenda_colors');
        if (\is_array($settingAgendaColors)) {
            $agendaColors = array_merge($defaultColors, $settingAgendaColors);
        }

        $colorKeyMap = [
            'global' => 'platform',
        ];

        $colorKey = $colorKeyMap[$eventType] ?? $eventType;

        return $agendaColors[$colorKey] ?? $defaultColors[$colorKey];
    }
}
