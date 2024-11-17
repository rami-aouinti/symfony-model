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
use App\Calendar\Infrastructure\Repository\CalendarStyleRepository;
use App\Platform\Application\Utils\Traits\JsonHelper;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\User\Transport\EventListener\Entity\UserListener;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity class CalendarStyle
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-21)
 * @since 0.1.1 (2022-11-21) Update to symfony 6.1
 * @since 0.1.0 (2021-12-30) First version.
 * @package App\Calendar\Domain\Entity
 */
#[ORM\Entity(repositoryClass: CalendarStyleRepository::class)]
#[ORM\Table(name: 'platform_calendar_style')]
#[ORM\EntityListeners([UserListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/calendar_styles/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['calendar_style_extended'],
            ]
        ),
        new Post(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),
        new Get(
            uriTemplate: '/calendar_styles/{id}/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['calendar_style_extended'],
            ],
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),
        new Put(
            normalizationContext: [
                'groups' => ['calendar_style'],
            ]
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['calendar_style'],
    ],
    order: [
        'id' => 'ASC',
    ],
)]
class CalendarStyle implements EntityInterface, \Stringable
{
    use Timestampable;
    use JsonHelper;

    final public const array CRUD_FIELDS_ADMIN = [];

    final public const array CRUD_FIELDS_REGISTERED = ['id', 'name', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_INDEX = ['id', 'name', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_NEW = ['id', 'name', 'configJson'];

    final public const array CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const array CRUD_FIELDS_DETAIL = ['id', 'name', 'updatedAt', 'createdAt', 'configJson'];

    final public const array CRUD_FIELDS_FILTER = ['name', 'updatedAt', 'createdAt'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['calendar_style', 'calendar_style_extended'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['calendar_style', 'calendar_style_extended'])]
    private string $name;

    /**
     * @var array<string|int|float|bool> $config
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['calendar_style', 'calendar_style_extended'])]
    private array $config = [];

    /**
     * __toString method.
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Gets the id of this calendar style.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the name of this calendar style.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the id of this calendar style.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * Sets the config.
     *
     * @param array<string|int|float|bool> $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

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
}
