<?php

declare(strict_types=1);

namespace App\Tag\Domain\Entity;

use App\Platform\Domain\Entity\Traits\Timestampable;
use App\Platform\Domain\Entity\Traits\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * Class Tag
 * @package App\Tag\Domain\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_tag')]
class Tag implements JsonSerializable
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'Tag',
        'Tag.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private readonly string $name;

    /**
     * @throws Throwable
     */
    public function __construct(string $name)
    {
        $this->id = $this->createUuid();
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
