<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Track\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * TrackEHotpotatoes.
 */
#[ORM\Table(name: 'track_e_hotpotatoes')]
#[ORM\Index(columns: ['exe_user_id'], name: 'idx_tehp_user_id')]
#[ORM\Index(columns: ['c_id'], name: 'idx_tehp_c_id')]
#[ORM\Entity]
class TrackEHotpotatoes
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'exe_user_id', type: 'integer', nullable: true)]
    protected ?int $exeUserId = null;

    #[ORM\Column(name: 'exe_date', type: 'datetime', nullable: false)]
    protected DateTime $exeDate;

    #[ORM\Column(name: 'c_id', type: 'integer', nullable: false)]
    protected int $cId;

    #[ORM\Column(name: 'score', type: 'smallint', nullable: false)]
    protected int $score;

    #[ORM\Column(name: 'max_score', type: 'smallint', nullable: false)]
    protected int $maxScore;

    /**
     * Set title.
     *
     * @return TrackEHotpotatoes
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set exeUserId.
     *
     * @return TrackEHotpotatoes
     */
    public function setExeUserId(int $exeUserId)
    {
        $this->exeUserId = $exeUserId;

        return $this;
    }

    /**
     * Get exeUserId.
     *
     * @return int
     */
    public function getExeUserId()
    {
        return $this->exeUserId;
    }

    /**
     * Set exeDate.
     *
     * @return TrackEHotpotatoes
     */
    public function setExeDate(DateTime $exeDate)
    {
        $this->exeDate = $exeDate;

        return $this;
    }

    /**
     * Get exeDate.
     *
     * @return DateTime
     */
    public function getExeDate()
    {
        return $this->exeDate;
    }

    /**
     * Set cId.
     *
     * @return TrackEHotpotatoes
     */
    public function setCId(int $cId)
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * Get cId.
     *
     * @return int
     */
    public function getCId()
    {
        return $this->cId;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getMaxScore(): int
    {
        return $this->maxScore;
    }

    public function setMaxScore(int $maxScore): self
    {
        $this->maxScore = $maxScore;

        return $this;
    }
}
