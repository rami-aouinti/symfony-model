<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CourseBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\CoreBundle\ApiResource\CourseTool;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\Tool;
use App\CoreBundle\Filter\CidFilter;
use App\CoreBundle\Filter\SidFilter;
use App\CoreBundle\State\CToolStateProvider;
use App\CourseBundle\Repository\CToolRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Platform\Domain\Entity\ResourceShowCourseResourcesInSessionInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
    ],
    normalizationContext: [
        'groups' => ['ctool:read'],
    ],
    output: CourseTool::class,
    provider: CToolStateProvider::class,
)]
#[ORM\Table(name: 'c_tool')]
#[ORM\Index(columns: ['c_id'], name: 'course')]
#[ORM\Index(columns: ['session_id'], name: 'session_id')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CToolRepository::class)]
#[ApiFilter(CidFilter::class)]
#[ApiFilter(SidFilter::class)]
#[ApiFilter(OrderFilter::class, properties: [
    'position' => 'ASC',
])]
class CTool extends AbstractResource implements ResourceInterface, ResourceShowCourseResourcesInSessionInterface, Stringable
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'title', type: 'text', nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'visibility', type: 'boolean', nullable: true)]
    protected ?bool $visibility = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'tools')]
    #[ORM\JoinColumn(name: 'c_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Gedmo\SortableGroup]
    protected Course $course;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[Gedmo\SortableGroup]
    protected ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: Tool::class)]
    #[ORM\JoinColumn(name: 'tool_id', referencedColumnName: 'id', nullable: false)]
    protected Tool $tool;

    #[Gedmo\SortablePosition]
    #[ORM\Column(name: 'position', type: 'integer')]
    protected int $position;

    public function __construct()
    {
        $this->visibility = true;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getNameToTranslate(): string
    {
        return ucfirst(str_replace('_', ' ', $this->title));
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIid(): ?int
    {
        return $this->iid;
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

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session = null): self
    {
        $this->session = $session;

        return $this;
    }

    public function getVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getTool(): Tool
    {
        return $this->tool;
    }

    public function setTool(Tool $tool): self
    {
        $this->tool = $tool;

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

    public function getResourceIdentifier(): int
    {
        return $this->iid;
    }

    public function getResourceName(): string
    {
        return $this->getTitle();
    }

    public function setResourceName(string $name): self
    {
        return $this->setTitle($name);
    }
}
