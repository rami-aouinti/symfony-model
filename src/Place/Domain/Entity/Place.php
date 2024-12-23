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

namespace App\Place\Domain\Entity;

use App\Place\Application\Service\Entity\PlaceLoaderService;
use App\Place\Infrastructure\DataType\Point;
use App\Platform\Application\Utils\Constants\Code;
use App\Platform\Application\Utils\GPSConverter;
use CrEOF\Spatial\PHP\Types\Geometry\Point as CrEOFSpatialPHPTypesGeometryPoint;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Base entity superclass.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2022-05-21)
 * @package App\Calendar\Domain\Entity\Base
 */
#[ORM\MappedSuperclass]
abstract class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(name: 'geoname_id', type: 'integer')]
    protected int $geonameId;

    #[ORM\Column(type: 'string', length: 1024)]
    protected string $name;

    #[ORM\Column(name: 'ascii_name', type: 'string', length: 1024)]
    protected string $asciiName;

    #[ORM\Column(name: 'alternate_names', type: 'string', length: 4096)]
    protected string $alternateNames;

    #[ORM\Column(type: 'point')]
    protected CrEOFSpatialPHPTypesGeometryPoint $coordinate;

    #[ORM\Column(name: 'feature_class', type: 'string', length: 1)]
    protected string $featureClass;

    #[ORM\Column(name: 'feature_code', type: 'string', length: 10)]
    protected string $featureCode;

    #[ORM\Column(name: 'country_code', type: 'string', length: 2)]
    protected string $countryCode;

    #[ORM\Column(type: 'string', length: 200)]
    protected string $cc2;

    #[ORM\Column(type: 'bigint', nullable: true)]
    protected ?string $population = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $elevation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $dem = null;

    #[ORM\Column(type: 'string', length: 40)]
    protected string $timezone;

    #[ORM\Column(name: 'modification_date', type: 'date')]
    protected DateTime $modificationDate;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    protected ?string $admin1Code = null;

    #[ORM\Column(type: 'string', length: 80, nullable: true)]
    protected ?string $admin2Code = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    protected ?string $admin3Code = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    protected ?string $admin4Code = null;

    protected float $distanceDb = .0;

    protected float $distanceMeter = .0;

    protected ?string $direction = null;

    protected ?float $degree = null;

    protected int $relevance = 0;

    protected ?PlaceP $cityP = null;

    protected ?PlaceA $cityA = null;

    protected PlaceA|PlaceP|null $district = null;

    protected PlaceA|PlaceP|null $city = null;

    protected ?PlaceA $state = null;

    /**
     * @var PlaceH[]
     */
    protected array $lakes = [];

    /**
     * @var PlaceL[]
     */
    protected array $parks = [];

    /**
     * @var PlaceP[]
     */
    protected array $places = [];

    /**
     * @var PlaceS[]
     */
    protected array $spots = [];

    /**
     * @var PlaceT[]
     */
    protected array $mountains = [];

    /**
     * @var PlaceV[]
     */
    protected array $forests = [];

    protected ?string $country = null;

    protected ?string $populationAdmin = null;

    protected bool $isCity = false;

    protected string $templateAddName = '%s, %s';

    /**
     * Sets the id of this place.
     *
     * @return $this
     */
    public function setIdTmp(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the id of this place.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the geo name id of this place.
     */
    public function getGeonameId(): int
    {
        return $this->geonameId;
    }

    /**
     * Sets the geo name id of this place.
     *
     * @return $this
     */
    public function setGeonameId(int $geonameId): self
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    /**
     * Gets the name of this place.
     */
    public function getName(bool $withFeature = false, bool $withProperties = false): string
    {
        /* Remove some special strings */
        $name = str_replace(
            [
                ', Kurort',
            ],
            '',
            $this->name
        );

        if ($withProperties) {
            if (in_array($this->getFeatureCode(), Code::FEATURE_CODES_T_HILLS) && $this->getElevationHill() > 0) {
                $name = sprintf('%s (%d m)', $name, $this->getElevationHill());
            }
        }

        return $withFeature ? sprintf('%s [%s:%s]', $name, $this->getFeatureClass(), $this->getFeatureCode()) : $name;
    }

    /**
     * Gets the full name of this place.
     *
     * @throws Exception
     */
    public function getNameFull(bool $detailed = false, ?self $placeSource = null, bool $withProperties = false): string
    {
        /* Add current name (Place[AHLPRSTUV]). */
        $name = $this->getName($detailed, $withProperties);

        /* Add district name. */
        $this->addNameAfter($name, $this->getDistrict(), $detailed, $withProperties);

        /* Add city name. */
        $this->addNameAfter($name, $this->getCity(), $detailed, $withProperties);

        /* Add state name. */
        $this->addNameAfter($name, $this->getState(), $detailed, $withProperties);

        /* Add country. */
        $this->addNameAfter($name, $this->getCountry($detailed), $detailed, $withProperties);

        /* Add PlaceL. */
        $this->addNameBefore($name, $this->getFirstPark(true, $placeSource), $detailed, $withProperties);

        /* Add PlaceT. */
        $this->addNameBefore($name, $this->getFirstMountain(true, $placeSource), $detailed, $withProperties);

        /* Add PlaceS. */
        $this->addNameBefore($name, $this->getFirstSpot(true, $placeSource), $detailed, $withProperties);

        /* Add PlaceV. */
        $this->addNameBefore($name, $this->getFirstForest(true, $placeSource), $detailed, $withProperties);

        return $this->trim($name);
    }

    /**
     * Sets the name of this place.
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the ascii name of this place.
     */
    public function getAsciiName(): string
    {
        return $this->asciiName;
    }

    /**
     * Sets the ascii name of this place.
     *
     * @return $this
     */
    public function setAsciiName(string $asciiName): self
    {
        $this->asciiName = $asciiName;

        return $this;
    }

    /**
     * Gets the alternate name of this place.
     */
    public function getAlternateNames(): string
    {
        return $this->alternateNames;
    }

    /**
     * Sets the alternate name of this place.
     *
     * @return $this
     */
    public function setAlternateNames(string $alternateNames): self
    {
        $this->alternateNames = $alternateNames;

        return $this;
    }

    /**
     * Gets the point of this place.
     */
    public function getCoordinate(): CrEOFSpatialPHPTypesGeometryPoint
    {
        return $this->coordinate;
    }

    /**
     * Sets the point of this place.
     *
     * @return $this
     */
    public function setCoordinate(CrEOFSpatialPHPTypesGeometryPoint $coordinate): self
    {
        $this->coordinate = $coordinate;

        return $this;
    }

    /**
     * Helper function: Returns the latitude position (y position) of this place.
     *
     * TODO: Change latitude and longitude within db. It is reversed.
     */
    public function getLatitude(): float
    {
        return $this->getCoordinate()->getLongitude();
    }

    /**
     * Helper function: Returns the longitude position (x position) of this place.
     *
     * TODO: Change latitude and longitude within db. It is reversed.
     */
    public function getLongitude(): float
    {
        return $this->getCoordinate()->getLatitude();
    }

    /**
     * Gets the feature class of this place.
     */
    public function getFeatureClass(): string
    {
        return $this->featureClass;
    }

    /**
     * Sets the feature class of this place.
     *
     * @return $this
     */
    public function setFeatureClass(string $featureClass): self
    {
        $this->featureClass = $featureClass;

        return $this;
    }

    /**
     * Gets the feature code of this place.
     */
    public function getFeatureCode(): string
    {
        return $this->featureCode;
    }

    /**
     * Sets the feature code of this place.
     *
     * @return $this
     */
    public function setFeatureCode(string $featureCode): self
    {
        $this->featureCode = $featureCode;

        return $this;
    }

    /**
     * Gets the country code of this place.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Sets the country code of this place.
     *
     * @return $this
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Gets the cc2 of this place.
     */
    public function getCc2(): string
    {
        return $this->cc2;
    }

    /**
     * Sets the cc2 of this place.
     *
     * @return $this
     */
    public function setCc2(string $cc2): self
    {
        $this->cc2 = $cc2;

        return $this;
    }

    /**
     * Gets the population of this place.
     */
    public function getPopulation(bool $intval = false): string|int|null
    {
        return $intval ? intval($this->population) : $this->population;
    }

    /**
     * Sets the population of this place.
     *
     * @return $this
     */
    public function setPopulation(string|int|null $population): self
    {
        $this->population = strval($population);

        return $this;
    }

    /**
     * Gets the elevation of this place.
     */
    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    /**
     * Gets the elevation of this place if this is a hill, mountain, etc.
     */
    public function getElevationHill(): int|false|null
    {
        if (!in_array($this->getFeatureCode(), Code::FEATURE_CODES_T_HILLS)) {
            return null;
        }

        if ($this->elevation <= 0) {
            return false;
        }

        return $this->elevation;
    }

    /**
     * Sets the elevation of this place.
     *
     * @return $this
     */
    public function setElevation(?int $elevation): self
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Gets the dem of this place.
     */
    public function getDem(): ?int
    {
        return $this->dem;
    }

    /**
     * Sets the dem of this place.
     *
     * @return $this
     */
    public function setDem(?int $dem): self
    {
        $this->dem = $dem;

        return $this;
    }

    /**
     * Gets the timezone of this place.
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Sets the timezone of this place.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Gets the modification date of this place.
     */
    public function getModificationDate(): DateTime
    {
        return $this->modificationDate;
    }

    /**
     * Sets the modification date of this place.
     *
     * @return $this
     */
    public function setModificationDate(DateTime $modificationDate): self
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Gets admin1 code of this place.
     */
    public function getAdmin1Code(): ?string
    {
        return $this->admin1Code;
    }

    /**
     * Sets admin1 code of this place.
     *
     * @return $this
     */
    public function setAdmin1Code(?string $admin1Code): self
    {
        $this->admin1Code = $admin1Code;

        return $this;
    }

    /**
     * Gets admin2 code of this place.
     */
    public function getAdmin2Code(): ?string
    {
        return $this->admin2Code;
    }

    /**
     * Sets admin2 code of this place.
     *
     * @return $this
     */
    public function setAdmin2Code(?string $admin2Code): self
    {
        $this->admin2Code = $admin2Code;

        return $this;
    }

    /**
     * Gets admin3 code of this place.
     */
    public function getAdmin3Code(): ?string
    {
        return $this->admin3Code;
    }

    /**
     * Sets admin3 code of this place.
     *
     * @return $this
     */
    public function setAdmin3Code(?string $admin3Code): self
    {
        $this->admin3Code = $admin3Code;

        return $this;
    }

    /**
     * Gets admin4 code of this place.
     */
    public function getAdmin4Code(): ?string
    {
        return $this->admin4Code;
    }

    /**
     * Sets admin4 code of this place.
     *
     * @return $this
     */
    public function setAdmin4Code(?string $admin4Code): self
    {
        $this->admin4Code = $admin4Code;

        return $this;
    }

    /**
     * Gets distance of this place (if given from select query to a given place). Not used for db.
     */
    public function getDistanceDb(): float
    {
        return $this->distanceDb;
    }

    /**
     * Gets distance of this place (if given from select query to a given place). Not used for db.
     */
    public function getDistanceDbInMeter(int $decimal = 1, bool $withUnit = true, bool $withDirection = false): float|string
    {
        $mDegree = 42_000_000 / 360;

        $distance = round($mDegree * $this->getDistanceDb(), $decimal);

        $distanceFormatted = $withUnit ? sprintf(sprintf('%%.%df m', $decimal), $distance) : $distance;

        if ($withDirection) {
            $distanceFormatted = sprintf('%s (%s)', $distanceFormatted, 'NE');
        }

        return $distanceFormatted;
    }

    /**
     * Sets distance of this place (if given from select query to a given place). Not used for db.
     *
     * @return $this
     */
    public function setDistanceDb(float $distanceDb): self
    {
        $this->distanceDb = $distanceDb;

        return $this;
    }

    /**
     * Gets distance in m of this place (if given from select query to a given place). Not used for db.
     */
    public function getDistanceMeter(?int $decimal = null, ?string $unit = null, int $divider = 1, bool $withDirection = false): float|string
    {
        $distanceMeter = $this->distanceMeter / $divider;

        if ($decimal !== null) {
            $distanceMeter = sprintf(sprintf('%%.%df', $decimal), $distanceMeter);
        }

        if ($unit) {
            $distanceMeter = str_replace('.', ',', sprintf('%s %s', $distanceMeter, $unit));
        }

        if ($withDirection && $this->getDirection() !== null && $this->distanceMeter !== .0) {
            $distanceMeter = sprintf('<div style="line-height: .875em;">%s</div><div style="font-size: .75em; line-height: .75em; padding-top: .5em;">- %s -</div>', $distanceMeter, $this->getDirection());
        }

        return $distanceMeter;
    }

    /**
     * Sets distance in m of this place (if given from select query to a given place). Not used for db.
     *
     * @return $this
     */
    public function setDistanceMeter(float $distanceMeter): self
    {
        $this->distanceMeter = $distanceMeter;

        return $this;
    }

    /**
     * Gets direction this place (if given). Not used for db.
     */
    public function getDirection(): ?string
    {
        return $this->direction;
    }

    /**
     * Sets direction of this place (if given). Not used for db.
     *
     * @return $this
     */
    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get relevance of this place (if given). Not used for db.
     */
    public function getDegree(): ?float
    {
        return $this->degree;
    }

    /**
     * Set relevance of this place (if given). Not used for db.
     */
    public function setDegree(?float $degree): self
    {
        $this->degree = $degree;

        return $this;
    }

    /**
     * Get relevance of this place. Not used for db.
     */
    public function getRelevance(): int
    {
        return $this->relevance;
    }

    /**
     * Set relevance of this place. Not used for db.
     */
    public function setRelevance(int $relevance): self
    {
        $this->relevance = $relevance;

        return $this;
    }

    /**
     * Returns if distance is lower than given.
     */
    public function withinTheDistance(self $place, int $maxDistanceRural, int $maxDistanceCity): bool
    {
        return match (true) {
            $place->isCity() => $this->getDistanceMeter() <= $maxDistanceCity,
            default => $this->getDistanceMeter() <= $maxDistanceRural,
        };
    }

    /**
     * Gets city (P) of this place. Not used for db.
     */
    public function getCityP(): ?PlaceP
    {
        return $this->cityP;
    }

    /**
     * Sets city (P) of this place. Not used for db.
     *
     * @return $this
     */
    public function setCityP(?PlaceP $cityP): self
    {
        $this->cityP = $cityP;

        return $this;
    }

    /**
     * Gets city (A) of this place. Not used for db.
     */
    public function getCityA(): ?PlaceA
    {
        return $this->cityA;
    }

    /**
     * Sets city (A) of this place. Not used for db.
     *
     * @return $this
     */
    public function setCityA(?PlaceA $cityA): self
    {
        $this->cityA = $cityA;

        return $this;
    }

    /**
     * Gets district of this place. Not used for db.
     */
    public function getDistrict(): PlaceA|PlaceP|null
    {
        return $this->district;
    }

    /**
     * Sets district of this place. Not used for db.
     *
     * @return $this
     */
    public function setDistrict(PlaceA|PlaceP|null $district): self
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Gets city of this place. Not used for db.
     */
    public function getCity(): PlaceA|PlaceP|null
    {
        return $this->city;
    }

    /**
     * Sets city of this place. Not used for db.
     *
     * @return $this
     */
    public function setCity(PlaceA|PlaceP|null $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Gets state of this place. Not used for db.
     */
    public function getState(): ?PlaceA
    {
        return $this->state;
    }

    /**
     * Sets state of this place. Not used for db.
     *
     * @return $this
     */
    public function setState(?PlaceA $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Gets lakes of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceH[]
     */
    public function getLakes(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->parks. */
        return $this->filter($this->lakes, $max, $filterIds);
    }

    /**
     * Gets first lake of this place. Not used for db.
     */
    public function getFirstLake(bool $checkDistance = false, ?self $placeSource = null): ?PlaceH
    {
        if ($placeSource instanceof PlaceH) {
            return $placeSource;
        }

        if ($placeSource !== null) {
            return null;
        }

        if (count($this->lakes) <= 0) {
            return null;
        }

        $firstLake = $this->lakes[0];

        if (!$checkDistance || $firstLake->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_LAKE_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_LAKE_METER_TITLE_CITY)) {
            return $firstLake;
        }

        return null;
    }

    /**
     * Sets parks of this place. Not used for db.
     *
     * @param PlaceH[] $lakes
     * @return $this
     */
    public function setLakes(array $lakes): self
    {
        $this->lakes = $lakes;

        return $this;
    }

    /**
     * Adds lake to this place. Not used for db.
     *
     * @return $this
     */
    public function addLake(PlaceH $lake): self
    {
        if ($lake->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_LAKE_METER_LIST_RURAL) {
            return $this;
        }

        $this->lakes[] = $lake;

        return $this;
    }

    /**
     * Gets parks of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceL[]
     */
    public function getParks(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->parks. */
        return $this->filter($this->parks, $max, $filterIds);
    }

    /**
     * Gets first park of this place. Not used for db.
     */
    public function getFirstPark(bool $checkDistance = false, ?self $placeSource = null): ?PlaceL
    {
        if ($placeSource instanceof PlaceL) {
            return $placeSource;
        }

        if ($placeSource !== null) {
            return null;
        }

        if (count($this->parks) <= 0) {
            return null;
        }

        $firstPark = $this->parks[0];

        if (!$checkDistance || $firstPark->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_PARK_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_PARK_METER_TITLE_CITY)) {
            return $firstPark;
        }

        return null;
    }

    /**
     * Sets parks of this place. Not used for db.
     *
     * @param PlaceL[] $parks
     * @return $this
     */
    public function setParks(array $parks): self
    {
        $this->parks = $parks;

        return $this;
    }

    /**
     * Adds park to this place. Not used for db.
     *
     * @return $this
     */
    public function addPark(PlaceL $park): self
    {
        if ($park->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_PARK_METER_LIST_RURAL) {
            return $this;
        }

        $this->parks[] = $park;

        return $this;
    }

    /**
     * Gets places of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceP[]
     */
    public function getPlaces(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->places. */
        return $this->filter($this->places, $max, $filterIds);
    }

    /**
     * Gets first place of this place. Not used for db.
     */
    public function getFirstPlace(bool $checkDistance = false): ?PlaceP
    {
        if (count($this->places) <= 0) {
            return null;
        }

        $firstPlace = $this->places[0];

        if (!$checkDistance || $firstPlace->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_PLACE_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_PLACE_METER_TITLE_CITY)) {
            return $firstPlace;
        }

        return null;
    }

    /**
     * Sets places of this place. Not used for db.
     *
     * @param PlaceP[] $places
     * @return $this
     */
    public function setPlaces(array $places): self
    {
        $this->places = $places;

        return $this;
    }

    /**
     * Adds place to this place. Not used for db.
     *
     * @return $this
     */
    public function addPlace(PlaceP $place): self
    {
        if ($place->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_PLACE_METER_LIST_RURAL) {
            return $this;
        }

        $this->places[] = $place;

        return $this;
    }

    /**
     * Gets spots of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceS[]
     */
    public function getSpots(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->spots. */
        return $this->filter($this->spots, $max, $filterIds);
    }

    /**
     * Gets first spot of this place. Not used for db.
     */
    public function getFirstSpot(bool $checkDistance = false, ?self $placeSource = null): ?PlaceS
    {
        if ($placeSource instanceof PlaceS) {
            return $placeSource;
        }

        if ($placeSource !== null) {
            return null;
        }

        if (count($this->spots) <= 0) {
            return null;
        }

        $firstSpot = $this->spots[0];

        if (!$checkDistance || $firstSpot->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_SPOT_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_SPOT_METER_TITLE_CITY)) {
            return $firstSpot;
        }

        return null;
    }

    /**
     * Sets spots of this place. Not used for db.
     *
     * @param PlaceS[] $spots
     * @return $this
     */
    public function setSpots(array $spots): self
    {
        $this->spots = $spots;

        return $this;
    }

    /**
     * Adds spot to this place. Not used for db.
     *
     * @return $this
     */
    public function addSpot(PlaceS $spot): self
    {
        if ($spot->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_SPOT_METER_LIST_RURAL) {
            return $this;
        }

        $this->spots[] = $spot;

        return $this;
    }

    /**
     * Gets mountains of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceT[]
     */
    public function getMountains(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->mountains. */
        return $this->filter($this->mountains, $max, $filterIds);
    }

    /**
     * Gets first mountain of this place. Not used for db.
     */
    public function getFirstMountain(bool $checkDistance = false, ?self $placeSource = null): ?PlaceT
    {
        if ($placeSource instanceof PlaceT) {
            return $placeSource;
        }

        if ($placeSource !== null) {
            return null;
        }

        if (count($this->mountains) <= 0) {
            return null;
        }

        $firstMountain = $this->mountains[0];

        if (!$checkDistance || $firstMountain->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_MOUNTAIN_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_MOUNTAIN_METER_TITLE_CITY)) {
            return $firstMountain;
        }

        return null;
    }

    /**
     * Sets mountains of this place. Not used for db.
     *
     * @param PlaceT[] $mountains
     * @return $this
     */
    public function setMountains(array $mountains): self
    {
        $this->mountains = $mountains;

        return $this;
    }

    /**
     * Adds mountain to this place. Not used for db.
     *
     * @return $this
     */
    public function addMountain(PlaceT $mountain): self
    {
        if ($mountain->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_MOUNTAIN_METER_LIST_RURAL) {
            return $this;
        }

        $this->mountains[] = $mountain;

        return $this;
    }

    /**
     * Gets forests of this place. Not used for db.
     *
     * @param int[]|int|null $filterIds
     * @return PlaceV[]
     */
    public function getForests(?int $max = null, array|int|null $filterIds = null): array
    {
        /** @phpstan-ignore-next-line → Filter result depends to given $this->forests. */
        return $this->filter($this->forests, $max, $filterIds);
    }

    /**
     * Gets first forest of this place. Not used for db.
     */
    public function getFirstForest(bool $checkDistance = false, ?self $placeSource = null): ?PlaceV
    {
        if ($placeSource instanceof PlaceV) {
            return $placeSource;
        }

        if ($placeSource !== null) {
            return null;
        }

        if (count($this->forests) <= 0) {
            return null;
        }

        $firstForest = $this->forests[0];

        if (!$checkDistance || $firstForest->withinTheDistance($this, PlaceLoaderService::MAX_DISTANCE_FOREST_METER_TITLE_RURAL, PlaceLoaderService::MAX_DISTANCE_FOREST_METER_TITLE_CITY)) {
            return $firstForest;
        }

        return null;
    }

    /**
     * Sets forests of this place. Not used for db.
     *
     * @param PlaceV[] $forests
     * @return $this
     */
    public function setForests(array $forests): self
    {
        $this->forests = $forests;

        return $this;
    }

    /**
     * Adds forest to this place. Not used for db.
     *
     * @return $this
     */
    public function addForest(PlaceV $forest): self
    {
        if ($forest->getDistanceMeter() > PlaceLoaderService::MAX_DISTANCE_FOREST_METER_LIST_RURAL) {
            return $this;
        }

        $this->forests[] = $forest;

        return $this;
    }

    /**
     * Gets the translated country name of this place. Not used for db.
     */
    public function getCountry(bool $detailed = false): ?string
    {
        if ($this->country === null) {
            return null;
        }

        return $detailed ? sprintf('%s (COUNTRY)', $this->country) : $this->country;
    }

    /**
     * Sets the translated country name of this place. Not used for db.
     *
     * @return $this
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gets the admin (city) population of this place. Not used for db.
     */
    public function getPopulationAdmin(bool $intval = false): string|int|null
    {
        return $intval ? intval($this->populationAdmin) : $this->populationAdmin;
    }

    /**
     * Sets the admin (city) population of this place. Not used for db.
     *
     * @return $this
     */
    public function setPopulationAdmin(string|int|null $populationAdmin): self
    {
        $this->populationAdmin = strval($populationAdmin);

        return $this;
    }

    /**
     * Returns if this place is a city. Not used for db.
     */
    public function isCity(): bool
    {
        return $this->isCity;
    }

    /**
     * Sets if this place is a city. Not used for db.
     */
    public function setIsCity(bool $isCity): self
    {
        $this->isCity = $isCity;

        return $this;
    }

    /**
     * Returns if this place is an admin place. Not used for db.
     */
    public function isAdminPlace(): bool
    {
        if ($this->getFeatureClass() === Code::FEATURE_CLASS_A) {
            return true;
        }

        if (
            $this->getFeatureClass() === Code::FEATURE_CLASS_P &&
            !in_array($this->getFeatureCode(), Code::FEATURE_CODES_P_DISTRICT_PLACES)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Gets the template add name. Not used for db.
     */
    public function getTemplateAddName(): string
    {
        return $this->templateAddName;
    }

    /**
     * Sets the template add name. Not used for db.
     *
     * @return $this
     */
    public function setTemplateAddName(string $templateAddName): self
    {
        $this->templateAddName = $templateAddName;

        return $this;
    }

    /**
     * Returns google link. Not used for db.
     *
     * @throws Exception
     */
    #[ArrayShape([
        'longitude' => 'string',
        'latitude' => 'string',
    ])]
    public function getGoogleLink(): string
    {
        $coordinateTranslated = new Point($this->coordinate->getX(), $this->coordinate->getY(), $this->coordinate->getSrid());

        return GPSConverter::decimalDegree2GoogleLink(
            $coordinateTranslated->getLongitude(),
            $coordinateTranslated->getLatitude(),
            $coordinateTranslated->getLongitudeDirection(),
            $coordinateTranslated->getLatitudeDirection()
        );
    }

    /**
     * Adds a new name before the existing name of this place (Spot, Mountain, Area, etc.).
     *
     * @throws Exception
     */
    protected function addNameBefore(string &$name, self|string|null $place, bool $detailed = false, bool $withProperties = false): void
    {
        if ($place === null) {
            return;
        }

        $addName = $place instanceof self ? $place->getName($detailed, $withProperties) : $place;

        if ($this->strContains($name, $addName)) {
            return;
        }

        $name = sprintf($this->templateAddName, $addName, $name);
    }

    /**
     * Adds a new name after the existing name of this place (state, country, etc.).
     *
     * @throws Exception
     */
    protected function addNameAfter(string &$name, self|string|null $place, bool $detailed = false, bool $withProperties = false): void
    {
        if ($place === null) {
            return;
        }

        $addName = $place instanceof self ? $place->getName($detailed, $withProperties) : $place;

        if ($this->strContains($name, $addName)) {
            return;
        }

        $name = sprintf($this->templateAddName, $name, $addName);
    }

    /**
     * Case insensitive $this->strContains.
     *
     * Replacement for: str_contains(strtolower($haystack), strtolower($needle));
     *
     * @throws Exception
     */
    protected function strContains(string $haystack, string $needle): bool
    {
        $return = preg_match(sprintf('~(^|,[ ]*)%s(,[ ]*|$)~i', preg_quote(strtolower($needle))), strtolower($haystack));

        if ($return === false) {
            throw new Exception(sprintf('Unable to search with preg_match (%s:%d).', __FILE__, __LINE__));
        }

        return $return > 0;
    }

    /**
     * Filter function
     *
     * @param Place[] $data
     * @param int[]|int|null $filterIds
     * @return Place[]
     */
    protected function filter(array $data, ?int $max = null, array|int|null $filterIds = null): array
    {
        if ($filterIds !== null) {
            if (!is_array($filterIds)) {
                $filterIds = [$filterIds];
            }

            $data = array_filter($data, fn (Place $place) => !in_array($place->getId(), $filterIds));
        }

        if ($max !== null) {
            $data = array_slice($data, 0, $max);
        }

        return $data;
    }

    /**
     * Trims given string.
     *
     * @throws Exception
     */
    protected function trim(string $string): string
    {
        $string = trim($string);

        $string = preg_replace('~^, ~', '', $string);
        if ($string === null) {
            throw new Exception(sprintf('Unable to replace comma (%s:%d).', __FILE__, __LINE__));
        }

        $string = preg_replace('~, $~', '', $string);
        if ($string === null) {
            throw new Exception(sprintf('Unable to replace comma (%s:%d).', __FILE__, __LINE__));
        }

        return $string;
    }
}
