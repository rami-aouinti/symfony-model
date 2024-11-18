<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\User;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\CoreBundle\Controller\Api\CreatePersonalFileAction;
use App\CoreBundle\Controller\Api\UpdatePersonalFileAction;
use App\CoreBundle\Entity\Listener\ResourceListener;
use App\CoreBundle\Repository\Node\PersonalFileRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonalFile
 *
 * @package App\CoreBundle\Entity\User
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ApiResource(
    operations: [
        new Put(
            controller: UpdatePersonalFileAction::class,
            security: "is_granted('EDIT', object.resourceNode)",
            deserialize: false
        ),
        new Get(security: "is_granted('VIEW', object.resourceNode)"),
        new Delete(security: "is_granted('DELETE', object.resourceNode)"),
        new Post(
            controller: CreatePersonalFileAction::class,
            openapi: new Operation(
                summary: 'Create a personal file',
                description: 'Upload a personal file with metadata such as title, comment, and links.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'title' => ['type' => 'string'],
                                    'comment' => ['type' => 'string'],
                                    'contentFile' => ['type' => 'string'],
                                    'uploadFile' => ['type' => 'string', 'format' => 'binary'],
                                    'parentResourceNodeId' => ['type' => 'integer'],
                                    'resourceLinkList' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'visibility' => ['type' => 'integer'],
                                                'c_id' => ['type' => 'integer'],
                                                'session_id' => ['type' => 'integer'],
                                            ],
                                        ],
                                    ],
                                ],
                                'required' => ['title', 'uploadFile'], // Champs obligatoires
                            ],
                            'example' => [
                                'title' => 'My Personal File',
                                'comment' => 'This is a personal document',
                                'contentFile' => 'Optional content as string',
                                'uploadFile' => '<binary>',
                                'parentResourceNodeId' => 123,
                                'resourceLinkList' => [
                                    ['visibility' => 1, 'c_id' => 10, 'session_id' => 20]
                                ],
                            ],
                        ],
                    ])
                )
            ),
            security: "is_granted('ROLE_USER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'personal_file:write'],
            ],
            deserialize: false
        ),
        new GetCollection(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: [
        'groups' => ['personal_file:read', 'resource_node:read'],
    ],
    denormalizationContext: [
        'groups' => ['personal_file:write'],
    ]
)]
#[ORM\Table(name: 'personal_file')]
#[ORM\EntityListeners([ResourceListener::class])]
#[ORM\Entity(repositoryClass: PersonalFileRepository::class)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: [
        'title' => 'partial',
        'resourceNode.parent' => 'exact',
    ]
)]
#[ApiFilter(
    filterClass: PropertyFilter::class
)]
#[ApiFilter(
    filterClass: OrderFilter::class,
    properties: [
        'id',
        'resourceNode.title',
        'resourceNode.createdAt',
        'resourceNode.firstResourceFile.size',
        'resourceNode.updatedAt',
    ]
)]
class PersonalFile extends AbstractResource implements ResourceInterface, Stringable
{
    use TimestampableEntity;

    #[Groups(['personal_file:read'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[Groups(['personal_file:read'])]
    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[Groups(['personal_file:read'])]
    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private ?string $comment = null;

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getResourceIdentifier(): int
    {
        return $this->getId();
    }

    public function getResourceName(): string
    {
        return $this->getTitle();
    }

    public function setResourceName(string $name): self
    {
        return $this->setTitle($name);
    }
}
