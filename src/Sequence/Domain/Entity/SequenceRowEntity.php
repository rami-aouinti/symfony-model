<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Sequence\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SequenceRowEntity
 *
 * @package App\Sequence\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'sequence_row_entity')]
#[ORM\Entity]
class SequenceRowEntity
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'c_id', type: 'integer')]
    protected int $cId;

    #[ORM\Column(name: 'session_id', type: 'integer')]
    protected int $sessionId;

    #[ORM\Column(name: 'row_id', type: 'integer')]
    protected int $rowId;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[ORM\ManyToOne(targetEntity: SequenceTypeEntity::class)]
    #[ORM\JoinColumn(name: 'sequence_type_entity_id', referencedColumnName: 'id')]
    protected ?SequenceTypeEntity $type = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCId()
    {
        return $this->cId;
    }

    public function setCId(int $cId): self
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setSessionId(int $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    public function setRowId(int $rowId): self
    {
        $this->rowId = $rowId;

        return $this;
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

    public function getType(): ?SequenceTypeEntity
    {
        return $this->type;
    }

    public function setType(?SequenceTypeEntity $type): self
    {
        $this->type = $type;

        return $this;
    }
}
