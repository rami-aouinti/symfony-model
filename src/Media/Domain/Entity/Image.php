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

namespace App\Media\Domain\Entity;

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
use App\Calendar\Domain\Entity\CalendarImage;
use App\Media\Infrastructure\Repository\ImageRepository;
use App\Platform\Application\Utils\FileNameConverter;
use App\Platform\Application\Utils\GPSConverter;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\User\Application\Voter\UserVoter;
use App\User\Domain\Entity\User;
use App\User\Transport\EventListener\Entity\UserListener;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity class Image
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.4 (2022-11-19)
 * @since 0.1.4 (2022-11-19) Update ApiPlatform.
 * @since 0.1.3 (2022-11-11) PHPStan refactoring.
 * @since 0.1.2 (2022-07-16) Change self::$path to string|null.
 * @since 0.1.1 (2022-01-29) Possibility to disable the JWT locally for debugging processes (#45)
 * @since 0.1.0 First version.
 * @package App\Calendar\Domain\Entity
 */
#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'platform_image')]
#[ORM\EntityListeners([UserListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    # Security filter for collection operations at App\Doctrine\CurrentUserExtension
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => ['image'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/images/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['image_extended'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/users/{id}/images.{_format}',
            uriVariables: [
                'id' => new Link(
                    fromProperty: 'images',
                    fromClass: User::class
                ),
            ],
        ),
        new Post(
            normalizationContext: [
                'groups' => ['image'],
            ],
            securityPostDenormalize: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_POST . '")',
            securityPostDenormalizeMessage: 'Only own images can be added.'
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['image'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_DELETE . '", object.user)',
            securityMessage: 'Only own images can be deleted.'
        ),
        new Get(
            normalizationContext: [
                'groups' => ['image'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_GET . '", object.user)',
            securityMessage: 'Only own images can be read.'
        ),
        new Get(
            uriTemplate: '/images/{id}/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['image_extended'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_GET . '", object.user)',
            securityMessage: 'Only own images can be read.'
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['image'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_PATCH . '", object.user)',
            securityMessage: 'Only own images can be modified.'
        ),
        new Put(
            normalizationContext: [
                'groups' => ['image'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_IMAGE_PUT . '", object.user)',
            securityMessage: 'Only own images can be modified.'
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['image'],
    ],
    order: [
        'id' => 'ASC',
    ],
)]
class Image implements EntityInterface, \Stringable
{
    use Timestampable;

    final public const array CRUD_FIELDS_ADMIN = ['id', 'user'];

    final public const array CRUD_FIELDS_REGISTERED = ['id', 'user', 'name', 'path', 'pathSource', 'pathSourcePreview', 'width', 'height', 'size', 'title', 'latitude', 'longitude', 'url', 'gpsHeight', 'iso', 'mime', 'place', 'placeDistrict', 'placeCity', 'placeState', 'placeCountry', 'placeTimezone', 'information', 'takenAt', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_INDEX = ['id', 'user', 'name', 'pathSourcePreview', 'width', 'height', 'size', 'title', 'latitude', 'longitude', 'information', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_NEW = ['id', 'user', 'path', 'title', 'url'];

    final public const array CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const array CRUD_FIELDS_DETAIL = ['id', 'user', 'path', 'width', 'height', 'size', 'title', 'latitude', 'longitude', 'url', 'gpsHeight', 'iso', 'mime', 'place', 'placeDistrict', 'placeCity', 'placeState', 'placeCountry', 'placeTimezone', 'information', 'takenAt', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_FILTER = ['user', 'width', 'height', 'size'];

    final public const string PATH_TYPE_SOURCE = 'source';

    final public const string PATH_TYPE_TARGET = 'target';

    final public const string PATH_TYPE_EXPECTED = 'expected';

    final public const string PATH_TYPE_COMPARE = 'compare';

    final public const string PATH_TYPE_AUTO = 'auto';

    final public const string PATH_DATA = 'data';

    final public const string PATH_IMAGES = 'images';

    final public const string PATH_DATA_IMAGES = 'data/images';

    final public const int WIDTH_400 = 400;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['image', 'image_extended'])]
    public ?User $user = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['image', 'image_extended'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['image', 'image_extended'])]
    private ?string $path = null;

    private ?string $pathSource = null;

    private ?string $pathTarget = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['image', 'image_extended'])]
    private ?int $width = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['image', 'image_extended'])]
    private ?int $height = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['image', 'image_extended'])]
    private ?int $size = null;

    /**
     * @var Collection<int, CalendarImage>
     */
    #[ORM\OneToMany(mappedBy: 'image', targetEntity: CalendarImage::class, orphanRemoval: true)]
    #[Groups(['image', 'image_extended'])]
    private Collection $calendarImages;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['image_extended'])]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['image_extended'])]
    private ?float $longitude = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $url = null;

    #[ORM\Column(name: 'gps_height', type: 'integer', nullable: true)]
    #[Groups(['image_extended'])]
    private ?int $gpsHeight = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['image_extended'])]
    private ?int $iso = null;

    #[ORM\Column(type: 'string', length: 63, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $mime = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $place = null;

    #[ORM\Column(name: 'place_district', type: 'string', length: 255, nullable: true)]
    private ?string $placeDistrict = null;

    #[ORM\Column(name: 'place_city', type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $placeCity = null;

    #[ORM\Column(name: 'place_state', type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $placeState = null;

    #[ORM\Column(name: 'place_country', type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $placeCountry = null;

    #[ORM\Column(name: 'place_timezone', type: 'string', length: 255, nullable: true)]
    #[Groups(['image_extended'])]
    private ?string $placeTimezone = null;

    /**
     * @var array<string, mixed> $information
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['image_extended'])]
    private array $information = [];

    #[ORM\Column(name: 'taken_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $takenAt = null;

    #[Pure]
    public function __construct()
    {
        $this->calendarImages = new ArrayCollection();
    }

    /**
     * __toString method.
     *
     * @throws Exception
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Gets the name of the image.
     *
     * @throws Exception
     */
    public function getName(): string
    {
        if ($this->path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        $array = explode('/', $this->path);

        return end($array);
    }

    /**
     * Gets the id of this image.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the user of this image.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Gets the user id of this calendar.
     *
     * @throws Exception
     */
    #[Groups(['image', 'image_extended'])]
    public function getUserId(): ?string
    {
        return $this->getUser()?->getId();
    }

    /**
     * Sets the user of this image.
     *
     * @return $this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the relative path of this image.
     *
     * @throws Exception
     */
    public function getPath(string $type = self::PATH_TYPE_SOURCE, bool $tmp = false, bool $test = false, string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, string $rootPath = '', ?int $width = null, ?CalendarImage $calendarImage = null): ?string
    {
        if ($this->path === null) {
            return null;
        }

        $path = match (true) {
            $type === self::PATH_TYPE_SOURCE && $this->pathSource !== null => $this->pathSource,
            $type === self::PATH_TYPE_TARGET && $this->pathTarget !== null => $this->pathTarget,
            default => $this->path,
        };

        $fileNameConverter = new FileNameConverter($path, $rootPath, $test);

        return $fileNameConverter->getFilename(
            $type,
            $width,
            $tmp,
            $test,
            $outputMode,
            $calendarImage ? strval($calendarImage->getId()) : null
        );
    }

    /**
     * Gets the absolute path of this image.
     *
     * @throws Exception
     */
    public function getPathFull(string $type = self::PATH_TYPE_SOURCE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        $path = $this->getPath($type, $tmp, $test, FileNameConverter::MODE_OUTPUT_ABSOLUTE, $rootPath, $width, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Gets the relative or absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathSource(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        $path = $this->getPath(self::PATH_TYPE_SOURCE, $tmp, $test, $outputMode, $rootPath, $width, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Gets the relative or absolute source path of this image (preview placeholder).
     *
     * @throws Exception
     */
    public function getPathSourcePreview(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathSource($outputMode, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Gets the relative or absolute source path of this image with 400px width.
     *
     * @throws Exception
     */
    public function getPathSource400(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathSource($outputMode, $test, $rootPath, $tmp, self::WIDTH_400, $calendarImage);
    }

    /**
     * Gets the relative or absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathTarget(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        $path = $this->getPath(self::PATH_TYPE_TARGET, $tmp, $test, $outputMode, $rootPath, $width, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Gets the relative or absolute source path of this image (preview placeholder).
     *
     * @throws Exception
     */
    public function getPathTargetPreview(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathTarget($outputMode, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Gets the relative or absolute source path of this image with 400px width.
     *
     * @throws Exception
     */
    public function getPathTarget400(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathTarget($outputMode, $test, $rootPath, $tmp, self::WIDTH_400, $calendarImage);
    }

    /**
     * Gets the relative or absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathExpected(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        $path = $this->getPath(self::PATH_TYPE_EXPECTED, $tmp, $test, $outputMode, $rootPath, $width, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Gets the relative or absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathCompare(string $outputMode = FileNameConverter::MODE_OUTPUT_FILE, bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        $path = $this->getPath(self::PATH_TYPE_COMPARE, $tmp, $test, $outputMode, $rootPath, $width, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Gets the absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathSourceFull(bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathSource(FileNameConverter::MODE_OUTPUT_ABSOLUTE, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Gets the absolute target path of this image.
     *
     * @throws Exception
     */
    public function getPathTargetFull(bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathTarget(FileNameConverter::MODE_OUTPUT_ABSOLUTE, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Gets the absolute source path of this image.
     *
     * @throws Exception
     */
    public function getPathExpectedFull(bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathExpected(FileNameConverter::MODE_OUTPUT_ABSOLUTE, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Gets the absolute target path of this image.
     *
     * @throws Exception
     */
    public function getPathCompareFull(bool $test = false, string $rootPath = '', bool $tmp = false, ?int $width = null, ?CalendarImage $calendarImage = null): string
    {
        return $this->getPathCompare(FileNameConverter::MODE_OUTPUT_ABSOLUTE, $test, $rootPath, $tmp, $width, $calendarImage);
    }

    /**
     * Sets the relative path of this image.
     *
     * @return $this
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Sets the relative path of this image.
     *
     * @return $this
     */
    public function setPathSource(string $pathSource): self
    {
        $this->pathSource = $pathSource;

        return $this;
    }

    /**
     * Sets the relative path of this image.
     *
     * @return $this
     */
    public function setPathTarget(string $pathTarget): self
    {
        $this->pathTarget = $pathTarget;

        return $this;
    }

    /**
     * Gets the width of this image.
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Sets the width of this image.
     *
     * @return $this
     */
    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Gets the height of this image.
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * Sets the height of this image.
     *
     * @return $this
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Gets the size of this image.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Sets the size of this image.
     *
     * @return $this
     */
    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Gets all related calendar images from this image.
     *
     * @return Collection<int, CalendarImage>
     */
    public function getCalendarImages(): Collection
    {
        return $this->calendarImages;
    }

    /**
     * Gets all related calendar images as simple id list.
     *
     * @return Collection<int, int>
     */
    #[Groups(['image', 'image_extended'])]
    public function getCalendarImageIds(): Collection
    {
        return $this->getCalendarImages()->map(fn (CalendarImage $calendarImage) => $calendarImage->getId());
    }

    /**
     * Adds a related calendar image to this image.
     *
     * @return $this
     */
    public function addCalendarImage(CalendarImage $calendarImage): self
    {
        if (!$this->calendarImages->contains($calendarImage)) {
            $this->calendarImages[] = $calendarImage;
            $calendarImage->setImage($this);
        }

        return $this;
    }

    /**
     * Removes a given calendar image from this image.
     *
     * @return $this
     * @throws Exception
     */
    public function removeCalendarImage(CalendarImage $calendarImage): self
    {
        if ($this->calendarImages->removeElement($calendarImage)) {
            // set the owning side to null (unless already changed)
            if ($calendarImage->getImage() === $this) {
                $calendarImage->setImage(null);
            }
        }

        return $this;
    }

    /**
     * Gets the latitude of this image.
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude of this image.
     *
     * @return $this
     */
    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Gets the longitude of this image.
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude of this image.
     *
     * @return $this
     */
    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Returns the full position of this image.
     *
     * @throws Exception
     */
    public function getFullPosition(): string
    {
        if ($this->getLatitude() === null || $this->getLongitude() === null) {
            return '';
        }

        return sprintf(
            '%s %s',
            GPSConverter::decimalDegree2dms($this->getLatitude(), $this->getLatitude() < 0 ? GPSConverter::DIRECTION_SOUTH : GPSConverter::DIRECTION_NORTH),
            GPSConverter::decimalDegree2dms($this->getLongitude(), $this->getLongitude() < 0 ? GPSConverter::DIRECTION_WEST : GPSConverter::DIRECTION_EAST)
        );
    }

    /**
     * Gets the title of this image.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title of this image.
     *
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the url of this image.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sets the title of this image.
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the gps height of this image.
     */
    public function getGpsHeight(): ?int
    {
        return $this->gpsHeight;
    }

    /**
     * Sets the gps height of this image.
     *
     * @return $this
     */
    public function setGpsHeight(?int $gpsHeight): self
    {
        $this->gpsHeight = $gpsHeight;

        return $this;
    }

    /**
     * Gets the iso of this image.
     */
    public function getIso(): ?int
    {
        return $this->iso;
    }

    /**
     * Sets the iso of this image.
     *
     * @return $this
     */
    public function setIso(?int $iso): self
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * Gets the mime type of this image.
     */
    public function getMime(): ?string
    {
        return $this->mime;
    }

    /**
     * Sets the mime type of this image.
     *
     * @return $this
     */
    public function setMime(?string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Gets the place name of this image.
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }

    /**
     * Sets the place name of this image.
     *
     * @return $this
     */
    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Gets the district name of this image.
     */
    public function getPlaceDistrict(): ?string
    {
        return $this->placeDistrict;
    }

    /**
     * Sets the district name of this image.
     *
     * @return $this
     */
    public function setPlaceDistrict(?string $placeDistrict): self
    {
        $this->placeDistrict = $placeDistrict;

        return $this;
    }

    /**
     * Gets the city of this image.
     */
    public function getPlaceCity(): ?string
    {
        return $this->placeCity;
    }

    /**
     * Sets the city of this image.
     *
     * @return $this
     */
    public function setPlaceCity(?string $placeCity): self
    {
        $this->placeCity = $placeCity;

        return $this;
    }

    /**
     * Gets the state of this image.
     */
    public function getPlaceState(): ?string
    {
        return $this->placeState;
    }

    /**
     * Sets the state of this image.
     *
     * @return $this
     */
    public function setPlaceState(?string $placeState): self
    {
        $this->placeState = $placeState;

        return $this;
    }

    /**
     * Gets the country of this image.
     */
    public function getPlaceCountry(): ?string
    {
        return $this->placeCountry;
    }

    /**
     * Sets the country of this image.
     *
     * @return $this
     */
    public function setPlaceCountry(?string $placeCountry): self
    {
        $this->placeCountry = $placeCountry;

        return $this;
    }

    /**
     * Gets the timezone of this image.
     */
    public function getPlaceTimezone(): ?string
    {
        return $this->placeTimezone;
    }

    /**
     * Sets the timezone of this image.
     *
     * @return $this
     */
    public function setPlaceTimezone(?string $placeTimezone): self
    {
        $this->placeTimezone = $placeTimezone;

        return $this;
    }

    /**
     * Gets the information of this image.
     *
     * @return array<string, mixed>|null
     */
    public function getInformation(): ?array
    {
        return $this->information;
    }

    /**
     * Sets the information of this image.
     *
     * @param array<string, mixed> $information
     * @return $this
     */
    public function setInformation(array $information): self
    {
        $this->information = $information;

        return $this;
    }

    /**
     * Gets the time taken at of this image.
     */
    public function getTakenAt(): ?DateTimeImmutable
    {
        return $this->takenAt;
    }

    /**
     * Sets the time taken at of this image.
     *
     * @return $this
     */
    public function setTakenAt(?DateTimeImmutable $takenAt): self
    {
        $this->takenAt = $takenAt;

        return $this;
    }
}
