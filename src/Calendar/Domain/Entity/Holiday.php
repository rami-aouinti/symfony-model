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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Calendar\Infrastructure\Repository\HolidayRepository;
use App\Calendar\Transport\EventListener\Entity\HolidayListener;
use App\Platform\Application\Utils\ArrayToObject;
use App\Platform\Application\Utils\Traits\JsonHelper;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\User\Transport\EventListener\Entity\UserListener;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity class Holiday
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-21)
 * @since 0.1.1 (2022-11-21) Update to symfony 6.1
 * @since 0.1.0 (2021-12-30) First version.
 * @package App\Calendar\Domain\Entity
 */
#[ORM\Entity(repositoryClass: HolidayRepository::class)]
#[ORM\Table(name: 'platform_holiday')]
#[ORM\EntityListeners([UserListener::class, HolidayListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/holidays/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['holiday_extended'],
            ]
        ),
        new Post(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),
        new Get(
            uriTemplate: '/holidays/{id}/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['holiday_extended'],
            ]
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),
        new Put(
            normalizationContext: [
                'groups' => ['holiday'],
            ]
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['holiday'],
    ],
    order: [
        'id' => 'ASC',
    ],
)]
class Holiday implements EntityInterface, \Stringable
{
    use Timestampable;
    use JsonHelper;

    final public const array CRUD_FIELDS_ADMIN = [];

    final public const CRUD_FIELDS_REGISTERED = ['id', 'holidayGroup', 'name', 'date', 'yearly', 'type', 'configJson', 'updatedAt', 'createdAt'];

    final public const CRUD_FIELDS_INDEX = ['id', 'holidayGroup', 'name', 'date', 'yearly', 'type', 'configJson', 'updatedAt', 'createdAt'];

    final public const CRUD_FIELDS_NEW = ['id', 'holidayGroup', 'name', 'date', 'yearly', 'type', 'configJson'];

    final public const CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const CRUD_FIELDS_DETAIL = ['id', 'holidayGroup', 'name', 'date', 'yearly', 'type', 'configJson', 'updatedAt', 'createdAt'];

    final public const CRUD_FIELDS_FILTER = ['holidayGroup', 'name', 'date', 'yearly', 'type'];

    final public const FIELD_TYPE_PUBLIC_DATE = 0;

    final public const FIELD_TYPE_NON_PUBLIC_DATE = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['holiday', 'holiday_extended'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: HolidayGroup::class, inversedBy: 'holidays')]
    #[Groups(['holiday', 'holiday_extended'])]
    private ?HolidayGroup $holidayGroup = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['holiday', 'holiday_extended'])]
    private string $name;

    #[ORM\Column(type: 'date')]
    #[Groups(['holiday', 'holiday_extended'])]
    private DateTimeInterface $date;

    /**
     * @var array<string|int|float|bool> $config
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['holiday', 'holiday_extended'])]
    private array $config = [];

    #[ORM\Column(type: 'boolean')]
    #[Groups(['holiday', 'holiday_extended'])]
    private bool $yearly = false;

    private ArrayToObject $configObject;

    #[ORM\Column(type: 'integer')]
    private int $type = 0;

    /**
     * __toString method.
     */
    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->getName(), $this->getDate()->format('d.n.Y'));
    }

    /**
     * Gets the id of this holiday.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the holiday group of this holiday.
     */
    public function getHolidayGroup(): ?HolidayGroup
    {
        return $this->holidayGroup;
    }

    /**
     * Gets the holiday group id of this holiday.
     */
    #[Groups(['holiday', 'holiday_extended'])]
    public function getHolidayGroupId(): ?int
    {
        return $this->getHolidayGroup()?->getId();
    }

    /**
     * Sets the holiday group of this holiday.
     *
     * @return $this
     */
    public function setHolidayGroup(?HolidayGroup $holidayGroup): self
    {
        $this->holidayGroup = $holidayGroup;

        return $this;
    }

    /**
     * Gets the name of this holiday.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of this holiday.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the date of this holiday.
     */
    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Sets the date of this holiday.
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
