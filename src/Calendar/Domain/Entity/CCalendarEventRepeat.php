<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Calendar\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CCalendarEventRepeat.
 */
#[ORM\Table(name: 'c_calendar_event_repeat')]
#[ORM\Entity]
class CCalendarEventRepeat
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\ManyToOne(targetEntity: CCalendarEvent::class, inversedBy: 'repeatEvents')]
    #[ORM\JoinColumn(name: 'cal_id', referencedColumnName: 'iid')]
    protected CCalendarEvent $event;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'cal_type', type: 'string', length: 20, nullable: true)]
    protected ?string $calType = null;

    #[ORM\Column(name: 'cal_end', type: 'integer', nullable: true)]
    protected ?int $calEnd = null;

    #[ORM\Column(name: 'cal_frequency', type: 'integer', nullable: true)]
    protected ?int $calFrequency = null;

    #[ORM\Column(name: 'cal_days', type: 'string', length: 7, nullable: true)]
    protected ?string $calDays = null;

    public function setCalType(string $calType): self
    {
        $this->calType = $calType;

        return $this;
    }

    /**
     * Get calType.
     */
    public function getCalType(): ?string
    {
        return $this->calType;
    }

    public function setCalEnd(int $calEnd): self
    {
        $this->calEnd = $calEnd;

        return $this;
    }

    /**
     * Get calEnd.
     */
    public function getCalEnd(): ?int
    {
        return $this->calEnd;
    }

    public function setCalFrequency(int $calFrequency): self
    {
        $this->calFrequency = $calFrequency;

        return $this;
    }

    /**
     * Get calFrequency.
     */
    public function getCalFrequency(): ?int
    {
        return $this->calFrequency;
    }

    public function setCalDays(string $calDays): self
    {
        $this->calDays = $calDays;

        return $this;
    }

    /**
     * Get calDays.
     */
    public function getCalDays(): ?string
    {
        return $this->calDays;
    }

    public function getEvent(): CCalendarEvent
    {
        return $this->event;
    }
}
