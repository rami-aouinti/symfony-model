<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\TemplatesRepository;
use App\CoreBundle\Traits\UserTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Templates.
 */
#[ORM\Table(name: 'templates')]
#[ORM\Entity(repositoryClass: TemplatesRepository::class)]
class Templates
{
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 100, nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'description', type: 'string', length: 250, nullable: false)]
    protected string $description;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'templates', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'c_id', referencedColumnName: 'id')]
    protected Course $course;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\Column(name: 'ref_doc', type: 'integer', nullable: false)]
    protected int $refDoc;

    #[ORM\ManyToOne(targetEntity: Asset::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'image_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Asset $image = null;

    /**
     * Set title.
     *
     * @return Templates
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
     * Set description.
     *
     * @return Templates
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set refDoc.
     *
     * @return Templates
     */
    public function setRefDoc(int $refDoc)
    {
        $this->refDoc = $refDoc;

        return $this;
    }

    /**
     * Get refDoc.
     *
     * @return int
     */
    public function getRefDoc()
    {
        return $this->refDoc;
    }

    public function getImage(): ?Asset
    {
        return $this->image;
    }

    public function setImage(?Asset $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
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

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }
}
