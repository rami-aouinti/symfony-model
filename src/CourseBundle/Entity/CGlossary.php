<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\CoreBundle\Controller\Api\CreateCGlossaryAction;
use App\CoreBundle\Controller\Api\ExportCGlossaryAction;
use App\CoreBundle\Controller\Api\ExportGlossaryToDocumentsAction;
use App\CoreBundle\Controller\Api\GetGlossaryCollectionController;
use App\CoreBundle\Controller\Api\ImportCGlossaryAction;
use App\CoreBundle\Controller\Api\UpdateCGlossaryAction;
use App\CourseBundle\Repository\CGlossaryRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Platform\Domain\Entity\ResourceShowCourseResourcesInSessionInterface;
use ArrayObject;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Course glossary.
 */
#[ApiResource(
    shortName: 'Glossary',
    operations: [
        new Put(
            controller: UpdateCGlossaryAction::class,
            security: "is_granted('EDIT', object.resourceNode)",
            validationContext: [
                'groups' => ['media_object_create', 'glossary:write'],
            ],
            deserialize: false
        ),
        new Get(security: "is_granted('VIEW', object.resourceNode)"),
        new Delete(security: "is_granted('DELETE', object.resourceNode)"),
        new Post(
            controller: CreateCGlossaryAction::class,
            openapi: new Operation(
                summary: 'Create a glossary',
                description: 'Create a new glossary with the specified details.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'parentResourceNodeId' => ['type' => 'integer'],
                                    'resourceLinkList' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'visibility' => ['type' => 'integer'],
                                                'cid' => ['type' => 'integer'],
                                                'gid' => ['type' => 'integer'],
                                                'sid' => ['type' => 'integer'],
                                            ],
                                        ],
                                    ],
                                ],
                                'required' => ['name'],
                            ],
                        ],
                    ])
                )
            ),
            security: "is_granted('ROLE_CURRENT_COURSE_TEACHER') or is_granted('ROLE_CURRENT_COURSE_SESSION_TEACHER') or is_granted('ROLE_TEACHER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'glossary:write'],
            ],
            deserialize: false
        ),
        new GetCollection(
            controller: GetGlossaryCollectionController::class,
            openapi: new Operation(
                summary: 'Retrieve a collection of glossaries',
                parameters: [
                    new Parameter(
                        name: 'resourceNode.parent',
                        in: 'query',
                        description: 'Resource node Parent',
                        required: true,
                        schema: ['type' => 'integer']
                    ),
                    new Parameter(
                        name: 'cid',
                        in: 'query',
                        description: 'Course ID',
                        required: true,
                        schema: ['type' => 'integer']
                    ),
                    new Parameter(
                        name: 'sid',
                        in: 'query',
                        description: 'Session ID',
                        required: false,
                        schema: ['type' => 'integer']
                    ),
                    new Parameter(
                        name: 'q',
                        in: 'query',
                        description: 'Search term',
                        required: false,
                        schema: ['type' => 'string']
                    ),
                ]
            )
        ),
        new Post(
            uriTemplate: '/glossaries/import',
            controller: ImportCGlossaryAction::class,
            openapi: new Operation(
                responses: [
                    '200' => [
                        'description' => 'Glossaries imported successfully',
                    ],
                ],
                summary: 'Import a glossary',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            security: "is_granted('ROLE_CURRENT_COURSE_TEACHER') or is_granted('ROLE_CURRENT_COURSE_SESSION_TEACHER') or is_granted('ROLE_TEACHER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'glossary:write'],
            ],
            deserialize: false
        ),
        new Post(
            uriTemplate: '/glossaries/export',
            controller: ExportCGlossaryAction::class,
            openapi: new Operation(
                summary: 'Export glossaries',
                description: 'Export the glossaries to a downloadable format.'
            ),
            security: "is_granted('ROLE_CURRENT_COURSE_TEACHER') or is_granted('ROLE_CURRENT_COURSE_SESSION_TEACHER') or is_granted('ROLE_TEACHER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'glossary:write'],
            ],
            deserialize: false
        ),
        new Post(
            uriTemplate: '/glossaries/export_to_documents',
            controller: ExportGlossaryToDocumentsAction::class,
            openapi: new Operation(
                summary: 'Export glossaries to documents',
                description: 'Convert the glossaries into document files.'
            ),
            security: "is_granted('ROLE_CURRENT_COURSE_TEACHER') or is_granted('ROLE_CURRENT_COURSE_SESSION_TEACHER') or is_granted('ROLE_TEACHER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'glossary:write'],
            ],
            deserialize: false
        ),
    ],
    normalizationContext: [
        'groups' => ['glossary:read', 'resource_node:read'],
    ],
    denormalizationContext: [
        'groups' => ['glossary:write'],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['iid', 'name', 'createdAt', 'updatedAt'])]
#[ORM\Table(name: 'c_glossary')]
#[ORM\Entity(repositoryClass: CGlossaryRepository::class)]
class CGlossary extends AbstractResource implements ResourceInterface, ResourceShowCourseResourcesInSessionInterface, Stringable
{
    #[ApiProperty(identifier: true)]
    #[Groups(['glossary:read'])]
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[Groups(['glossary:read', 'glossary:write'])]
    #[Assert\NotBlank]
    #[ORM\Column(name: 'title', type: 'text', nullable: false)]
    protected string $title;

    #[Groups(['glossary:read', 'glossary:write'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    protected ?string $description = null;

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function getResourceIdentifier(): int|Uuid
    {
        return $this->getIid();
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
