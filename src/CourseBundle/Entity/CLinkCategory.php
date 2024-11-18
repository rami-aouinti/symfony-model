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
use App\CoreBundle\Controller\Api\CreateCLinkCategoryAction;
use App\CoreBundle\Controller\Api\UpdateCLinkCategoryAction;
use App\CoreBundle\Controller\Api\UpdateVisibilityLinkCategory;
use App\CoreBundle\Entity\Listener\ResourceListener;
use App\CourseBundle\Repository\CLinkCategoryRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Platform\Domain\Entity\ResourceShowCourseResourcesInSessionInterface;
use ArrayObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CLinkCategory.
 */
#[ApiResource(
    shortName: 'LinkCategories',
    operations: [
        new Put(
            controller: UpdateCLinkCategoryAction::class,
            security: "is_granted('EDIT', object.resourceNode)",
            validationContext: [
                'groups' => ['media_object_create', 'link_category:write'],
            ],
            deserialize: false
        ),
        new Put(
            uriTemplate: '/link_categories/{iid}/toggle_visibility',
            controller: UpdateVisibilityLinkCategory::class,
            security: "is_granted('EDIT', object.resourceNode)",
            deserialize: false
        ),
        new Get(security: "is_granted('VIEW', object.resourceNode)"),
        new Delete(security: "is_granted('DELETE', object.resourceNode)"),
        new Post(
            controller: CreateCLinkCategoryAction::class,
            openapi: new Operation(
                summary: 'Create a link category',
                description: 'Creates a new link category with the specified details.',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'category_title' => ['type' => 'string'],
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
                                'required' => ['category_title'], // Champs obligatoires
                            ],
                        ],
                    ])
                )
            ),
            security: "is_granted('ROLE_CURRENT_COURSE_TEACHER') or is_granted('ROLE_CURRENT_COURSE_SESSION_TEACHER') or is_granted('ROLE_TEACHER')",
            validationContext: [
                'groups' => ['Default', 'media_object_create', 'link_category:write'],
            ],
            deserialize: false
        ),
        new GetCollection(
            openapi: new Operation(
                summary: 'Retrieve a list of link categories',
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
                ]
            )
        ),
    ],
    normalizationContext: [
        'groups' => ['link_category:read', 'resource_node:read'],
    ],
    denormalizationContext: [
        'groups' => ['link_category:write'],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'resourceNode.parent' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['iid', 'resourceNode.title', 'resourceNode.createdAt', 'resourceNode.updatedAt'])]
#[ORM\Table(name: 'c_link_category')]
#[ORM\Entity(repositoryClass: CLinkCategoryRepository::class)]
#[ORM\EntityListeners([ResourceListener::class])]
class CLinkCategory extends AbstractResource implements ResourceInterface, ResourceShowCourseResourcesInSessionInterface, Stringable
{
    #[ApiProperty(identifier: true)]
    #[Groups(['link_category:read'])]
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[Groups(['link_category:read', 'link_category:write', 'link_category:browse'])]
    #[Assert\NotBlank]
    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[Groups(['link_category:read', 'link_category:write'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected ?string $description;

    #[Groups(['link_category:read', 'link_category:browse'])]
    protected bool $linkCategoryVisible = true;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: CLink::class)]
    protected Collection $links;

    public function __construct()
    {
        $this->description = '';
        $this->links = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getIid(): int
    {
        return $this->iid;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toggleVisibility(): void
    {
        $this->linkCategoryVisible = !$this->getFirstResourceLink()->getVisibility();
    }

    public function getLinkCategoryVisible(): bool
    {
        $this->linkCategoryVisible = (bool)$this->getFirstResourceLink()->getVisibility();

        return $this->linkCategoryVisible;
    }

    /**
     * @return CLink[]|Collection
     */
    public function getLinks(): array|Collection
    {
        return $this->links;
    }

    public function getResourceIdentifier(): int
    {
        return $this->iid;
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
