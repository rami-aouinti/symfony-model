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
 * Defines the properties of the Tag entity to represent the post tags.
 *
 * See https://symfony.com/doc/current/doctrine.html#creating-an-entity-class
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
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
        // This entity implements JsonSerializable (http://php.net/manual/en/class.jsonserializable.php)
        // so this method is used to customize its JSON representation when json_encode()
        // is called, for example in tags|json_encode (templates/form/fields.html.twig)

        return $this->name;
    }
}
