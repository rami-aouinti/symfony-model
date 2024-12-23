<?php

declare(strict_types=1);

namespace App\Event\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Event\Infrastructure\Repository\EventRepository;
use App\Platform\Application\Utils\ArrayToObject;
use App\Platform\Application\Utils\Traits\JsonHelper;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\User\Application\Voter\UserVoter;
use App\User\Domain\Entity\User;
use App\User\Transport\EventListener\Entity\UserListener;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\Pure;
use JsonException;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity class Event
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.3 (2022-11-11)
 * @since 0.1.3 (2022-11-21) Update to symfony 6.1
 * @since 0.1.2 (2022-11-11) PHPStan refactoring.
 * @since 0.1.1 (2022-01-29) Possibility to disable the JWT locally for debugging processes (#45)
 * @since 0.1.0 First version.
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'platform_event')]
#[ORM\EntityListeners([UserListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['event'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/events/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['event_extended'],
            ]
        ),
        new Post(
            normalizationContext: [
                'groups' => ['event'],
            ],
            securityPostDenormalize: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_POST . '")',
            securityPostDenormalizeMessage: 'Only own events can be added.'
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['event'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_DELETE . '", object.user)',
            securityMessage: 'Only own events can be deleted.'
        ),
        new Get(
            normalizationContext: [
                'groups' => ['event'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_GET . '", object.user)',
            securityMessage: 'Only own events can be read.'
        ),
        new Get(
            uriTemplate: '/events/{id}/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves an extended Event resource.'
                    ),
                ],
                summary: 'Retrieves an extended Event resource.',
            ),
            normalizationContext: [
                'groups' => ['event_extended'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_GET . '", object.user)',
            securityMessage: 'Only own events can be read.'
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['event'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_PATCH . '", object.user)',
            securityMessage: 'Only own events can be modified.'
        ),
        new Put(
            normalizationContext: [
                'groups' => ['event'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_EVENT_PUT . '", object.user)',
            securityMessage: 'Only own events can be modified.'
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['event'],
    ],
    order: [
        'id' => 'ASC',
    ],
)]
class Event implements EntityInterface, Stringable
{
    use Timestampable;
    use JsonHelper;

    final public const array CRUD_FIELDS_ADMIN = ['id', 'user'];

    final public const array CRUD_FIELDS_REGISTERED = ['id', 'name', 'type', 'user', 'date', 'yearly', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_INDEX = ['id', 'name', 'type', 'user', 'date', 'yearly', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_NEW = ['id', 'name', 'type', 'user', 'date', 'yearly', 'configJson'];

    final public const array CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const array CRUD_FIELDS_DETAIL = ['id', 'name', 'type', 'user', 'date', 'yearly', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_FILTER = ['name', 'type', 'user', 'date', 'yearly', 'updatedAt', 'createdAt'];

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('event')]
    public ?User $user = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['event', 'event_extended'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['event', 'event_extended'])]
    private string $name;

    #[ORM\Column(type: 'integer')]
    #[Groups(['event', 'event_extended'])]
    private int $type;

    #[ORM\Column(type: 'date')]
    #[Groups(['event', 'event_extended'])]
    private DateTimeInterface $date;

    /**
     * @var array<string|int|float|bool> $config
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['event', 'event_extended'])]
    private array $config = [
        'color' => '255,255,255,100',
    ];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['event', 'event_extended'])]
    private bool $yearly = false;

    private ArrayToObject $configObject;

    /**
     * __toString method.
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Gets the id of this event.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the user of this event.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Gets the user id of this event.
     *
     * @throws Exception
     */
    #[Pure]
    #[Groups(['event', 'event_extended'])]
    public function getUserId(): ?string
    {
        return $this->getUser()?->getId();
    }

    /**
     * Sets the user of this event.
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the name of this event.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of this event.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the type of this event.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Sets the type of this event.
     *
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the date of this event.
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the date of this event.
     *
     * @return $this
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Gets the config.
     *
     * @return array<string|int|float|bool>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Gets the config as object.
     *
     * @throws Exception
     */
    public function getConfigObject(): ArrayToObject
    {
        if (!isset($this->configObject)) {
            $this->configObject = new ArrayToObject($this->config);
        }

        return $this->configObject;
    }

    /**
     * Sets the config.
     *
     * @param array<string|int|float|bool> $config
     * @return $this
     * @throws Exception
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        $this->configObject = new ArrayToObject($config);

        return $this;
    }

    /**
     * Gets the config element as JSON.
     *
     * @throws Exception
     */
    public function getConfigJson(bool $beautify = true): string
    {
        return self::jsonEncode($this->config, $beautify, 2);
    }

    /**
     * Sets the config element from JSON.
     *
     * @throws JsonException
     * @return $this
     */
    public function setConfigJson(string $json): self
    {
        $this->config = self::jsonDecodeArray($json);

        return $this;
    }

    /**
     * Gets the config element as JSON.
     *
     * @throws Exception
     */
    public function getConfigJsonRaw(bool $beautify = true): string
    {
        return $this->getConfigJson(false);
    }

    /**
     * Sets the config element from JSON.
     *
     * @throws JsonException
     * @return $this
     */
    public function setConfigJsonRaw(string $json): self
    {
        return $this->setConfigJson($json);
    }

    /**
     * Gets the yearly status of this holiday.
     */
    public function getYearly(): ?bool
    {
        return $this->yearly;
    }

    /**
     * Sets the yearly status from this holiday.
     *
     * @return $this
     */
    public function setYearly(bool $yearly): self
    {
        $this->yearly = $yearly;

        return $this;
    }
}
