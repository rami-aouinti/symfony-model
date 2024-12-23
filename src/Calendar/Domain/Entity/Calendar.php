<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Calendar\Infrastructure\Repository\CalendarRepository;
use App\Platform\Application\Utils\ArrayToObject;
use App\Platform\Application\Utils\Traits\JsonHelper;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\User\Application\Voter\UserVoter;
use App\User\Domain\Entity\User;
use App\User\Transport\EventListener\Entity\UserListener;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Entity class Calendar
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.4 (2022-11-19)
 * @since 0.1.4 (2022-11-19) Update ApiPlatform.
 * @since 0.1.3 (2022-11-12) Upgrade to symfony 6.1
 * @since 0.1.2 (2022-11-11) PHPStan refactoring.
 * @since 0.1.1 (2022-01-29) Possibility to disable the JWT locally for debugging processes (#45)
 * @since 0.1.0 First version.
 * @package App\Calendar\Domain\Entity
 */
#[ORM\Entity(repositoryClass: CalendarRepository::class)]
#[ORM\Table(name: 'platform_calendar')]
#[ORM\EntityListeners([UserListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    # Security filter for collection operations at App\Doctrine\CurrentUserExtension
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['calendar'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/calendars/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['calendar_extended'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/calendars.{_format}',
            uriVariables: [
                'id' => new Link(
                    fromProperty: 'calendars',
                    fromClass: User::class
                ),
            ],
        ),
        new Post(
            normalizationContext: [
                'groups' => ['calendar'],
            ],
            securityPostDenormalize: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_POST . '")',
            securityPostDenormalizeMessage: 'Only own calendars can be added.'
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['calendar'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_DELETE . '", object.user)',
            securityMessage: 'Only own calendars can be deleted.'
        ),
        new Get(
            normalizationContext: [
                'groups' => ['calendar'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_GET . '", object.user)',
            securityMessage: 'Only own calendars can be read.'
        ),
        new Get(
            uriTemplate: '/calendars/{id}/extended.{_format}',
            uriVariables: [
                'id',
            ],
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['calendar_extended'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_GET . '", object.user)',
            securityMessage: 'Only own calendars can be read.'
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['calendar'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_PATCH . '", object.user)',
            securityMessage: 'Only own calendars can be modified.'
        ),
        new Put(
            normalizationContext: [
                'groups' => ['calendar'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_CALENDAR_PUT . '", object.user)',
            securityMessage: 'Only own calendars can be modified.'
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['calendar'],
    ],
    order: [
        'id' => 'ASC',
    ]
)]
class Calendar implements EntityInterface, \Stringable
{
    use Timestampable;
    use JsonHelper;

    final public const array CRUD_FIELDS_ADMIN = ['id', 'user'];

    final public const array CRUD_FIELDS_REGISTERED = ['id', 'name', 'title', 'subtitle', 'defaultYear', 'user', 'calendarStyle', 'holidayGroup', 'calendarImages', 'updatedAt', 'createdAt', 'configJson', 'published'];

    final public const array CRUD_FIELDS_INDEX = ['id', 'name', 'title', 'subtitle', 'defaultYear', 'user', 'calendarStyle', 'holidayGroup', 'updatedAt', 'createdAt', 'configJson', 'published'];

    final public const array CRUD_FIELDS_NEW = ['id', 'name', 'title', 'subtitle', 'defaultYear', 'user', 'calendarStyle', 'holidayGroup', 'configJson', 'published'];

    final public const array CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const array CRUD_FIELDS_DETAIL = ['id', 'name', 'title', 'subtitle', 'defaultYear', 'user', 'calendarStyle', 'holidayGroup', 'calendarImages', 'updatedAt', 'createdAt', 'configJson', 'published'];

    final public const array CRUD_FIELDS_FILTER = ['name', 'title', 'subtitle', 'defaultYear', 'user', 'published'];

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'calendars')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Groups(['calendar_extended', 'calendar'])]
    public ?User $user = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('calendar')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CalendarStyle::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Groups(['calendar_extended', 'calendar'])]
    private ?CalendarStyle $calendarStyle = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['calendar_extended', 'calendar'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['calendar_extended', 'calendar'])]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['calendar_extended', 'calendar'])]
    private ?string $subtitle = null;

    #[ORM\ManyToOne(targetEntity: HolidayGroup::class)]
    #[Groups(['calendar_extended', 'calendar'])]
    private ?HolidayGroup $holidayGroup = null;

    /**
     * @var Collection<int, CalendarImage> $calendarImages
     */
    #[ORM\OneToMany(mappedBy: 'calendar', targetEntity: CalendarImage::class, orphanRemoval: true)]
    #[MaxDepth(1)]
    #[Groups('calendar_extended')]
    #[ORM\OrderBy(value: [
        'month' => 'ASC',
    ])]
    private Collection $calendarImages;

    /**
     * @var array<string|int|float|bool> $config
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['calendar_extended', 'calendar'])]
    private array $config = [
        'backgroundColor' => '255,255,255,100',
        'printCalendarWeek' => true,
        'printWeekNumber' => true,
        'printQrCodeMonth' => true,
        'printQrCodeTitle' => true,
        'aspectRatio' => 1.414,
        'height' => 4000,
    ];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['calendar_extended', 'calendar'])]
    private bool $published = false;

    #[ORM\Column(name: 'default_year', type: 'integer')]
    private int $defaultYear;

    private ArrayToObject $configObject;

    public function __construct()
    {
        $this->calendarImages = new ArrayCollection();

        /* Sets default year */
        $this->defaultYear = intval(date('Y')) + 1;
    }

    /**
     * __toString method.
     */
    #[Pure]
    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->getName(), $this->getTitle());
    }

    /**
     * Gets the id of this calendar.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the user of this calendar.
     *
     * @throws Exception
     */
    public function getUser(): User
    {
        if (!isset($this->user)) {
            throw new Exception(sprintf('No user was configured (%s:%d)', __FILE__, __LINE__));
        }

        return $this->user;
    }

    /**
     * Gets the user id of this calendar.
     *
     * @throws Exception
     */
    #[Groups(['calendar_extended', 'calendar'])]
    public function getUserId(): ?string
    {
        return $this->getUser()->getId();
    }

    /**
     * Sets the user of this calendar.
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the calendar style of this calendar.
     */
    public function getCalendarStyle(): ?CalendarStyle
    {
        return $this->calendarStyle;
    }

    /**
     * Gets the calendar style id of this calendar.
     */
    #[Groups(['calendar_extended', 'calendar'])]
    public function getCalendarStyleId(): ?int
    {
        return $this->getCalendarStyle()?->getId();
    }

    /**
     * Sets the calendar style of this calendar.
     *
     * @return $this
     */
    public function setCalendarStyle(?CalendarStyle $calendarStyle): self
    {
        $this->calendarStyle = $calendarStyle;

        return $this;
    }

    /**
     * Gets the name of this calendar.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of this calendar.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the title of this calendar.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title of this calendar.
     *
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the subtitle of this calendar.
     */
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * Sets the subtitle of this calendar.
     *
     * @return $this
     */
    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Gets the holiday group of this calendar.
     */
    public function getHolidayGroup(): ?HolidayGroup
    {
        return $this->holidayGroup;
    }

    /**
     * Gets the holiday group id of this calendar.
     */
    #[Groups(['calendar_extended', 'calendar'])]
    public function getHolidayGroupId(): ?int
    {
        return $this->getHolidayGroup()?->getId();
    }

    /**
     * Sets the holiday group of this calendar.
     *
     * @return $this
     */
    public function setHolidayGroup(?HolidayGroup $holidayGroup): self
    {
        $this->holidayGroup = $holidayGroup;

        return $this;
    }

    /**
     * Gets the calendar images of this calendar.
     *
     * @return Collection<int, CalendarImage>
     */
    public function getCalendarImages(): Collection
    {
        return $this->calendarImages;
    }

    /**
     * Adds a calendar image to this calendar.
     *
     * @return $this
     */
    public function addCalendarImage(CalendarImage $calendarImage): self
    {
        if (!$this->calendarImages->contains($calendarImage)) {
            $this->calendarImages[] = $calendarImage;
            $calendarImage->setCalendar($this);
        }

        return $this;
    }

    /**
     * Removes a calendar image from this calendar.
     *
     * @return $this
     * @throws Exception
     */
    public function removeCalendarImage(CalendarImage $calendarImage): self
    {
        if ($this->calendarImages->removeElement($calendarImage)) {
            // set the owning side to null (unless already changed)
            if ($calendarImage->getCalendar() === $this) {
                $calendarImage->setCalendar(null);
            }
        }

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
     * @return $this
     */
    public function setConfigJsonRaw(string $json): self
    {
        return $this->setConfigJson($json);
    }

    /**
     * Gets the published status.
     */
    public function getPublished(): ?bool
    {
        return $this->published;
    }

    /**
     * Sets the published status.
     *
     * @return $this
     */
    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Gets the default year.
     */
    public function getDefaultYear(): int
    {
        return $this->defaultYear;
    }

    /**
     * Sets the default year.
     *
     * @return $this
     */
    public function setDefaultYear(int $defaultYear): self
    {
        $this->defaultYear = $defaultYear;

        return $this;
    }
}
