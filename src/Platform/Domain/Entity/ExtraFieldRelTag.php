<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\CoreBundle\Repository\ExtraFieldRelTagRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExtraFieldRelTag
 *
 * @package App\Platform\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'extra_field_rel_tag')]
#[ORM\Index(columns: ['field_id'], name: 'field')]
#[ORM\Index(columns: ['item_id'], name: 'item')]
#[ORM\Index(columns: ['tag_id'], name: 'tag')]
#[ORM\Index(columns: ['field_id', 'item_id', 'tag_id'], name: 'field_item_tag')]
#[ORM\Entity(repositoryClass: ExtraFieldRelTagRepository::class)]
class ExtraFieldRelTag
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ExtraField::class)]
    #[ORM\JoinColumn(name: 'field_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ExtraField $field;

    #[ORM\Column(name: 'item_id', type: 'integer', nullable: false)]
    protected int $itemId;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'extraFieldRelTags')]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Tag $tag = null;

    public function setItemId(int $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
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

    public function getField(): ExtraField
    {
        return $this->field;
    }

    public function setField(ExtraField $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
