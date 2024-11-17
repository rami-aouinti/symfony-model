<?php

declare(strict_types=1);

namespace App\Property\Domain\Entity;

use App\Platform\Domain\Entity\Traits\EntityIdTrait;
use App\Platform\Domain\Entity\Traits\EntityMetaTrait;
use App\Property\Infrastructure\Repository\PropertyDescriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PropertyDescription
 *
 * @package App\Property\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: PropertyDescriptionRepository::class)]
class PropertyDescription
{
    use EntityIdTrait;
    use EntityMetaTrait;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\OneToOne(inversedBy: 'propertyDescription', targetEntity: Property::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $property;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }
}
