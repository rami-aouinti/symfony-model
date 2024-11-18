<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Calendar\Domain\Entity\CCalendarEvent;
use App\Calendar\Domain\Entity\CCalendarEventAttachment;

class Agenda extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'agenda';
    }

    public function getIcon(): string
    {
        return 'mdi-calendar-text';
    }

    public function getLink(): string
    {
        return '/resources/ccalendarevent';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'events' => CCalendarEvent::class,
            'event_attachments' => CCalendarEventAttachment::class,
        ];
    }
}
