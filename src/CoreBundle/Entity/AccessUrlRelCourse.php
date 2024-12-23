<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Traits\CourseTrait;
use App\Platform\Domain\Entity\EntityAccessUrlInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * AccessUrlRelCourse.
 */
#[ORM\Table(name: 'access_url_rel_course')]
#[ORM\Entity]
class AccessUrlRelCourse implements EntityAccessUrlInterface, Stringable
{
    use CourseTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, cascade: ['persist'], inversedBy: 'urls')]
    #[ORM\JoinColumn(name: 'c_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?Course $course;

    #[ORM\ManyToOne(targetEntity: AccessUrl::class, cascade: ['persist'], inversedBy: 'courses')]
    #[ORM\JoinColumn(name: 'access_url_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?AccessUrl $url;

    public function __toString(): string
    {
        return '-';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?AccessUrl
    {
        return $this->url;
    }

    public function setUrl(?AccessUrl $url): self
    {
        $this->url = $url;

        return $this;
    }
}
