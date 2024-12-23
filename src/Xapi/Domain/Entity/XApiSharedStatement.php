<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\Xapi\Domain\Entity;

use App\CoreBundle\Repository\XApiSharedStatementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @package App\CoreBundle\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: XApiSharedStatementRepository::class)]
#[ORM\Index(columns: ['uuid'], name: 'idx_uuid')]
class XApiSharedStatement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column]
    private array $statement = [];

    #[ORM\Column]
    private ?bool $sent = null;

    public function __construct(array $statement, ?string $uuid = null, bool $sent = false)
    {
        $this->statement = $statement;
        $this->uuid = Uuid::fromString($uuid);
        $this->sent = $sent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getStatement(): array
    {
        return $this->statement;
    }

    public function setStatement(array $statement): static
    {
        $this->statement = $statement;

        return $this;
    }

    public function isSent(): ?bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): static
    {
        $this->sent = $sent;

        return $this;
    }
}
