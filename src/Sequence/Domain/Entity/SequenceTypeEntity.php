<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Sequence\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SequenceTypeEntity
 *
 * @package App\Sequence\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'sequence_type_entity')]
#[ORM\Entity]
class SequenceTypeEntity
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    protected string $description;

    #[ORM\Column(name: 'ent_table', type: 'string', length: 255, nullable: false)]
    protected string $entityTable;

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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityTable()
    {
        return $this->entityTable;
    }

    public function setEntityTable(string $entityTable): self
    {
        $this->entityTable = $entityTable;

        return $this;
    }
}
