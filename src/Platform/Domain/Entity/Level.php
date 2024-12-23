<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\CoreBundle\Entity\Portfolio\Profile;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;

/**
 * Skill level.
 */
#[ORM\Table(name: 'skill_level')]
#[ORM\Entity]
class Level implements Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[Gedmo\SortablePosition]
    #[ORM\Column(name: 'position', type: 'integer')]
    protected int $position;

    #[ORM\Column(name: 'short_title', type: 'string', length: 255, nullable: false)]
    protected string $shortTitle;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'levels')]
    #[ORM\JoinColumn(name: 'profile_id', referencedColumnName: 'id')]
    protected ?Profile $profile = null;

    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getShortTitle(): string
    {
        return $this->shortTitle;
    }

    public function setShortTitle(string $shortTitle): self
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }
}
