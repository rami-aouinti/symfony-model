<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CourseBundle\Entity\Student;

use App\CourseBundle\Repository\CStudentPublicationCorrectionRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'c_student_publication_correction')]
#[ORM\Entity(repositoryClass: CStudentPublicationCorrectionRepository::class)]
class CStudentPublicationCorrection extends AbstractResource implements ResourceInterface, Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    public function __toString(): string
    {
        return $this->title;
    }

    public function getResourceIdentifier(): int
    {
        return $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourceName(): string
    {
        return $this->getTitle();
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

    public function setResourceName(string $name): self
    {
        return $this->setTitle($name);
    }
}
