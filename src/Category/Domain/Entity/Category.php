<?php

declare(strict_types=1);

namespace App\Category\Domain\Entity;

use App\Category\Infrastructure\Repository\CategoryRepository;
use App\Platform\Domain\Entity\Traits\EntityNameTrait;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\Platform\Domain\Entity\Traits\Uuid;
use App\User\Domain\Entity\Traits\Blameable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * @package App\Category\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'platform_category')]
#[UniqueEntity('slug')]
class Category
{
    use Timestampable;
    use Uuid;
    use Blameable;
    use EntityNameTrait;

    final public const string MAPPED_BY = 'category';
    final public const string GETTER = 'getCategory';
    final public const string SETTER = 'setCategory';

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'Category',
        'Category.id',
    ])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['Category.parent'])]
    private ?self $parentCategory = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(mappedBy: 'parentCategory', targetEntity: self::class, cascade: ['persist', 'remove'])]
    #[Groups(['Category.children'])]
    private Collection $children;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->children = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getParentCategory(): ?self
    {
        return $this->parentCategory;
    }

    public function setParentCategory(?self $parentCategory): void
    {
        $this->parentCategory = $parentCategory;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParentCategory($this);
        }
    }

    public function removeChild(self $child): void
    {
        if ($this->children->removeElement($child)) {
            $child->setParentCategory(null);
        }
    }
}
