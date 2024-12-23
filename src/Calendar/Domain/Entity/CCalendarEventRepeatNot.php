<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Calendar\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CCalendarEventRepeatNot.
 */
#[ORM\Table(name: 'c_calendar_event_repeat_not')]
#[ORM\Entity]
class CCalendarEventRepeatNot
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\ManyToOne(targetEntity: CCalendarEvent::class, inversedBy: 'repeatEvents')]
    #[ORM\JoinColumn(name: 'cal_id', referencedColumnName: 'iid')]
    protected CCalendarEvent $event;

    #[ORM\Column(name: 'cal_date', type: 'integer')]
    protected int $calDate;

    public function setCalDate(int $calDate): self
    {
        $this->calDate = $calDate;

        return $this;
    }

    /**
     * Get calDate.
     */
    public function getCalDate(): int
    {
        return $this->calDate;
    }
}
