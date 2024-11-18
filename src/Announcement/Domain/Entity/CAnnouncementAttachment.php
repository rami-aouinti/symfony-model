<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Announcement\Domain\Entity;

use App\CourseBundle\Repository\CAnnouncementAttachmentRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * CAnnouncementAttachment.
 */
#[ORM\Table(name: 'c_announcement_attachment')]
#[ORM\Entity(repositoryClass: CAnnouncementAttachmentRepository::class)]
class CAnnouncementAttachment extends AbstractResource implements ResourceInterface, Stringable
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: false)]
    protected string $path;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    protected ?string $comment = null;

    #[ORM\Column(name: 'size', type: 'integer', nullable: false)]
    protected int $size;

    #[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: false)]
    protected string $filename;

    #[ORM\ManyToOne(targetEntity: CAnnouncement::class, inversedBy: 'attachments')]
    #[ORM\JoinColumn(name: 'announcement_id', referencedColumnName: 'iid', onDelete: 'CASCADE')]
    private ?CAnnouncement $announcement = null;

    public function __construct()
    {
        $this->comment = '';
    }

    public function __toString(): string
    {
        return $this->getFilename();
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getAnnouncement(): ?CAnnouncement
    {
        return $this->announcement;
    }

    public function setAnnouncement(?CAnnouncement $announcement): static
    {
        $this->announcement = $announcement;

        return $this;
    }

    public function getResourceIdentifier(): int
    {
        return $this->getIid();
    }

    public function getResourceName(): string
    {
        return $this->getFilename();
    }

    public function setResourceName(string $name): self
    {
        return $this->setFilename($name);
    }
}
