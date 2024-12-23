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

namespace App\Place\Application\Service;

use App\Calendar\Application\Config\SearchConfig;
use App\Place\Application\Service\Entity\PlaceLoaderService;
use App\Place\Domain\Entity\Place;
use App\Place\Domain\Entity\PlaceA;
use App\Platform\Application\Utils\Constants\Code;
use App\Platform\Application\Utils\GPSConverter;
use App\Platform\Application\Utils\Timer;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-05-23)
 * @package App\Service
 */
class LocationDataService
{
    final public const string KEY_NAME_FORMAT = 'format';
    final public const string KEY_NAME_TITLE = 'title';
    final public const string KEY_NAME_UNIT = 'unit';
    final public const string KEY_NAME_UNIT_BEFORE = 'unit-before';
    final public const string KEY_NAME_VALUE = 'value';
    final public const string KEY_NAME_VALUE_FORMATTED = 'value-formatted';

    final public const string KEY_NAME_PLACE_LATITUDE = 'place-latitude';
    final public const string KEY_NAME_PLACE_LONGITUDE = 'place-longitude';
    final public const string KEY_NAME_PLACE_LATITUDE_DMS = 'place-latitude-dms';
    final public const string KEY_NAME_PLACE_LONGITUDE_DMS = 'place-longitude-dms';
    final public const string KEY_NAME_PLACE_POINT = 'place-point';
    final public const string KEY_NAME_PLACE_GOOGLE_LINK = 'place-google-link';
    final public const string KEY_NAME_PLACE_OPENSTREETMAP_LINK = 'place-openstreetmap-link';
    final public const string KEY_NAME_PLACE = 'place';
    final public const string KEY_NAME_PLACE_FULL = 'place-full';
    final public const string KEY_NAME_PLACE_DISTRICT = 'place-district';
    final public const string KEY_NAME_PLACE_CITY = 'place-city';
    final public const string KEY_NAME_PLACE_STATE = 'place-state';
    final public const string KEY_NAME_PLACE_LAKE = 'place-lake';
    final public const string KEY_NAME_PLACE_PARK = 'place-park';
    final public const string KEY_NAME_PLACE_MOUNTAIN = 'place-mountain';
    final public const string KEY_NAME_PLACE_SPOT = 'place-spot';
    final public const string KEY_NAME_PLACE_FOREST = 'place-forest';
    final public const string KEY_NAME_PLACE_COUNTRY = 'place-country';
    final public const string KEY_NAME_PLACE_COUNTRY_CODE = 'place-country-code';
    final public const string KEY_NAME_PLACE_TIMEZONE = 'place-timezone';
    final public const string KEY_NAME_PLACE_POPULATION = 'place-population';
    final public const string KEY_NAME_PLACE_ELEVATION = 'place-elevation';
    final public const string KEY_NAME_PLACE_FEATURE_CLASS = 'place-feature-class';
    final public const string KEY_NAME_PLACE_FEATURE_CODE = 'place-feature-code';
    final public const string KEY_NAME_PLACE_FEATURE_NAME = 'place-feature-name';
    final public const string KEY_NAME_PLACE_DISTANCE_DB = 'place-distance-db';
    final public const string KEY_NAME_PLACE_DISTANCE_METER = 'place-distance-meter';
    final public const string KEY_NAME_PLACE_DEM = 'place-dem';
    final public const string KEY_NAME_PLACE_ADMIN1 = 'place-admin1';
    final public const string KEY_NAME_PLACE_ADMIN2 = 'place-admin2';
    final public const string KEY_NAME_PLACE_ADMIN3 = 'place-admin3';
    final public const string KEY_NAME_PLACE_ADMIN4 = 'place-admin4';
    final public const string KEY_NAME_CITY_OR_RURAL = 'city-or-rural';
    final public const string KEY_NAME_PLACES_NEAR = 'places-near';
    final public const string KEY_NAME_PLACE_TIME_TAKEN = 'time-taken';

    final public const int WIDTH_TITLE = 30;
    protected bool $debug = false;

    protected bool $verbose = false;

    public function __construct(
        protected PlaceLoaderService $placeLoaderService,
        protected TranslatorInterface $translator,
        protected SearchConfig $searchConfig
    ) {
        /* Set verbose if given */
        $this->setVerbose($this->searchConfig->isVerbose(), false);
    }

    /**
     * Sets debug mode.
     *
     * @return $this
     */
    public function setDebug(bool $debug, bool $withPlaceLoaderService = true): self
    {
        $this->debug = $debug;

        if ($withPlaceLoaderService) {
            $this->placeLoaderService->setDebug($debug);
        }

        return $this;
    }

    /**
     * Sets verbose mode.
     *
     * @return $this
     */
    public function setVerbose(bool $verbose, bool $withPlaceLoaderService = true): self
    {
        $this->verbose = $verbose;

        if ($withPlaceLoaderService) {
            $this->placeLoaderService->setVerbose($verbose);
        }

        return $this;
    }

    /**
     * Gets location place.
     *
     * @param array<string, Place[]> $data
     * @throws DoctrineDBALException
     * @throws NonUniqueResultException
     */
    public function getLocationPlace(float $latitude, float $longitude, array &$data = []): ?Place
    {
        return $this->placeLoaderService->findPlaceByPositionOrPlaceSource($latitude, $longitude, Code::FEATURE_CODES_P_ADMIN_PLACES, $data);
    }

    /**
     * Finds first location by name.
     *
     * @throws Exception
     */
    public function getLocationByName(string $name): ?Place
    {
        $places = $this->placeLoaderService->findByName($name);

        if (count($places) <= 0) {
            return null;
        }

        return $places[0];
    }

    /**
     * Finds all locations by name.
     *
     * @return Place[]
     * @throws Exception
     */
    public function getLocationsByName(string $name): array
    {
        return $this->placeLoaderService->findByName($name);
    }

    /**
     * Finds first location by code:id.
     *
     * @throws Exception
     */
    public function getPlaceByCodeId(string $codeId): ?Place
    {
        return $this->placeLoaderService->findByCodeId($codeId);
    }

    /**
     * Sets place information.
     *
     * @param array<string, array<string, mixed>> $dataReturn
     */
    public function setPlaceInformation(array &$dataReturn, Place $place, bool $addDistance = false): void
    {
        $dataReturn = array_merge($dataReturn, [
            self::KEY_NAME_PLACE_TIMEZONE => $this->getData('Place Timezone', $place->getTimezone(), '%s', null),
            self::KEY_NAME_PLACE_POPULATION => $this->getData('Place Population', $place->getPopulation(), '%s', null),
            self::KEY_NAME_PLACE_ELEVATION => $this->getData('Place Elevation', $place->getElevation(), '%s', ' m'),
            self::KEY_NAME_PLACE_FEATURE_CLASS => $this->getData('Place Feature Class', $place->getFeatureClass(), '%s', null),
            self::KEY_NAME_PLACE_FEATURE_CODE => $this->getData('Place Feature Code', $place->getFeatureCode(), '%s', null),
            self::KEY_NAME_PLACE_FEATURE_NAME => $this->getData('Place Feature Name', $this->translator->trans(sprintf('%s.%s', $place->getFeatureClass(), $place->getFeatureCode()), [], 'place'), '%s', null),
            self::KEY_NAME_PLACE_DEM => $this->getData('Digital Elevation Model', $place->getDem(), '%s', ' m'),
            self::KEY_NAME_PLACE_ADMIN1 => $this->getData('Admin1 Code', $place->getAdmin1Code(), '%s', null),
            self::KEY_NAME_PLACE_ADMIN2 => $this->getData('Admin2 Code', $place->getAdmin2Code(), '%s', null),
            self::KEY_NAME_PLACE_ADMIN3 => $this->getData('Admin3 Code', $place->getAdmin3Code(), '%s', null),
            self::KEY_NAME_PLACE_ADMIN4 => $this->getData('Admin4 Code', $place->getAdmin4Code(), '%s', null),
            self::KEY_NAME_CITY_OR_RURAL => $this->getData('City or rural', $place->isCity() ? 'Stadt' : 'Ländliche Gegend', '%s', null),
        ]);

        if ($addDistance) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_DISTANCE_DB => $this->getData('Place Distance DB', sprintf('%.6f', $place->getDistanceDb()), '%s', null),
                self::KEY_NAME_PLACE_DISTANCE_METER => $this->getData('Place Distance Meters', sprintf('%.2f', $place->getDistanceMeter()), '%.2f', ' m'),
            ]);
        }
    }

    /**
     * Gets full location data.
     *
     * @param array<string, Place[]> $placesNear
     * @return array<string, array<string, mixed>>
     * @throws DoctrineDBALException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function getLocationDataFull(float $latitude, float $longitude, array &$placesNear = [], ?Place $placeSource = null): array
    {
        $timer = Timer::start();
        $place = $this->placeLoaderService->findPlaceByPositionOrPlaceSource($latitude, $longitude, Code::FEATURE_CODES_P_ADMIN_PLACES, $placesNear, $placeSource);
        $time = Timer::stop($timer);

        $dataReturn = [];

        if ($place === null) {
            return $dataReturn;
        }

        /* PlaceP */
        if ($place->getDistrict() !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_DISTRICT => $this->getData('District', $place->getDistrict()->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceA */
        if ($place->getCity() !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_CITY => $this->getData('City', $place->getCity()->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceA */
        if ($place->getState() !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_STATE => $this->getData('Place State', $place->getState()->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceH */
        $firstLake = $place->getFirstLake(true, $placeSource);
        if ($firstLake !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_LAKE => $this->getData('Place Lake', $firstLake->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceL */
        $firstPark = $place->getFirstPark(true, $placeSource);
        if ($firstPark !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_PARK => $this->getData('Place Park', $firstPark->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceT */
        $firstMountain = $place->getFirstMountain(true, $placeSource);
        if ($firstMountain !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_MOUNTAIN => $this->getData('Place Mountain', $firstMountain->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceS */
        $firstSpot = $place->getFirstSpot(true, $placeSource);
        if ($firstSpot !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_SPOT => $this->getData('Place Spot', $firstSpot->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* PlaceV */
        $firstForest = $place->getFirstForest(true, $placeSource);
        if ($firstForest !== null) {
            $dataReturn = array_merge($dataReturn, [
                self::KEY_NAME_PLACE_FOREST => $this->getData('Place Forest', $firstForest->getName($this->verbose, true), '%s', null),
            ]);
        }

        /* Add country */
        $dataReturn = array_merge($dataReturn, [
            self::KEY_NAME_PLACE_COUNTRY => $this->getData('Place Country (translated)', $place->getCountry(), '%s', null),
            self::KEY_NAME_PLACE_COUNTRY_CODE => $this->getData('Place Country Code', $place->getCountryCode(), '%s', null),
        ]);

        /* Add full name */
        $dataReturn = array_merge($dataReturn, [
            self::KEY_NAME_PLACE_FULL => $this->getData('Place Full', $place->getNameFull($this->verbose, $placeSource, true), '%s', null),
        ]);

        /* Add place information */
        if ($placeSource !== null) {
            $this->setPlaceInformation($dataReturn, $placeSource);
        } else {
            $this->setPlaceInformation($dataReturn, $place, true);
        }

        $dataReturn = array_merge($dataReturn, [
            self::KEY_NAME_PLACE_LATITUDE => $this->getData('Latitude', $latitude, '%.5f', '°'),
            self::KEY_NAME_PLACE_LONGITUDE => $this->getData('Longitude', $longitude, '%.5f', '°'),
            self::KEY_NAME_PLACE_LATITUDE_DMS => $this->getData('Latitude DMS', GPSConverter::decimalDegree2dms($latitude, $latitude < 0 ? GPSConverter::DIRECTION_SOUTH : GPSConverter::DIRECTION_NORTH), '%s', null),
            self::KEY_NAME_PLACE_LONGITUDE_DMS => $this->getData('Longitude DMS', GPSConverter::decimalDegree2dms($longitude, $longitude < 0 ? GPSConverter::DIRECTION_WEST : GPSConverter::DIRECTION_EAST), '%s', null),
            self::KEY_NAME_PLACE_POINT => $this->getData('Location Point', sprintf('POINT(%.5f %.5f)', $latitude, $longitude), '%s', null),
            self::KEY_NAME_PLACE_GOOGLE_LINK => $this->getData(
                'Google Link',
                GPSConverter::decimalDegree2GoogleLink($latitude, $longitude),
                '%s',
                null
            ),
            self::KEY_NAME_PLACE_OPENSTREETMAP_LINK => $this->getData(
                'Openstreetmap Link',
                GPSConverter::decimalDegree2OpenstreetmapLink($latitude, $longitude),
                '%s',
                null
            ),
        ]);

        return array_merge($dataReturn, [
            self::KEY_NAME_PLACE_TIME_TAKEN => $this->getData('Time', $time, '%.3f', ' s'),
        ]);
    }

    /**
     * Gets location data.
     *
     * @param array<string, Place[]> $data
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getLocationData(float $latitude, float $longitude, array &$data = []): array
    {
        $locationData = $this->getLocationDataFull($latitude, $longitude, $data);

        $array = [];

        foreach ($locationData as $key => $value) {
            $array[$key] = $value['value'];
        }

        return $array;
    }

    /**
     * Calculate the distance between two points.
     *
     * @return array<string, float>
     */
    public static function getDistanceBetweenTwoPoints(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo, ?int $decimals = null): array
    {
        $theta = $longitudeFrom - $longitudeTo;

        /* Calculate distance. */
        if ($latitudeFrom === $latitudeTo && $longitudeFrom === $longitudeTo) {
            $distance = 0;
        } else {
            $distance = (sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo))) + (cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta)));
            $distance = acos($distance);
            $distance = rad2deg($distance);
        }

        /* Convert distances. */
        $miles = $distance * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;

        if ($decimals !== null) {
            $miles = round($miles, $decimals);
            $feet = round($feet, $decimals);
            $yards = round($yards, $decimals);
            $kilometers = round($kilometers, $decimals);
            $meters = round($meters, $decimals);
        }

        return compact('miles', 'feet', 'yards', 'kilometers', 'meters');
    }

    /**
     * Calculate the distance between two points in meters.
     */
    public static function getDistanceBetweenTwoPointsInMeter(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo, ?int $decimals = null): float
    {
        $distance = self::getDistanceBetweenTwoPoints($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $decimals);

        return $distance['meters'];
    }

    /**
     * Get relevance of given place
     */
    public static function getRelevance(string $search, string $sortBy = SearchConfig::ORDER_BY_RELEVANCE, ?Place $place = null): int
    {
        $relevance = 200000; /* 20.000 km (half earth circulation), to avoid relevance's < 0 */

        if ($place === null) {
            return $relevance;
        }

        /* The given place is equal to search name. */
        if (strtolower($place->getName()) === strtolower($search)) {
            $relevance += 10000;
        }

        /* The given place starts with search name. */
        if (str_starts_with(strtolower($place->getName()), strtolower($search))) {
            $relevance += 7500;
        }

        /* The search name is a word within the given place */
        if (preg_match(sprintf('~(^| )(%s)( |$)~i', $search), $place->getName())) {
            $relevance += 7500;
        }

        /* Admin Place */
        if ($place instanceof PlaceA) {
            $relevance += match ($place->getFeatureCode()) {
                Code::FEATURE_CODE_A_ADM1, Code::FEATURE_CODE_A_ADM1H => 5000,
                Code::FEATURE_CODE_A_ADM2, Code::FEATURE_CODE_A_ADM2H => 4500,
                Code::FEATURE_CODE_A_ADM3, Code::FEATURE_CODE_A_ADM3H => 4000,
                Code::FEATURE_CODE_A_ADM4, Code::FEATURE_CODE_A_ADM4H => 3500,
                Code::FEATURE_CODE_A_ADM5, Code::FEATURE_CODE_A_ADM5H => 3000,
                default => 2500,
            };
        }

        /* If this is not a hotel: +2000 */
        if ($place->getFeatureCode() !== Code::FEATURE_CODE_S_HTL) {
            $relevance += 2000;
        }

        /* Remove relevance:
         * 1 km:     -10
         * 10 km:    -100
         * 100 km:   -1000
         * 20000 km: -200000
         */
        if ($place->getDistanceMeter() !== null && $sortBy === SearchConfig::ORDER_BY_RELEVANCE_LOCATION) {
            $relevance -= intval(round(floatval($place->getDistanceMeter()) * 0.01, 0));
        }

        return $relevance;
    }

    /**
     * Gets the location data as an array from given id string and search config.
     *
     * @return array{locationData: array<string, mixed>, error: string|null}
     * @throws Exception
     */
    #[ArrayShape([
        'locationData' => 'array',
        'error' => 'null|string',
    ])]
    public function getLocationDetailDataFromIdString(string $idString): array
    {
        $place = $this->getPlaceByCodeId($idString);

        /* Unable to find place with given id string. */
        if ($place === null) {
            return [
                'locationData' => [],
                'error' => $this->translator->trans('general.notAvailableId', [
                    '%idString%' => $this->searchConfig->getIdString(),
                ], 'location'),
            ];
        }

        $placesNear = [];

        return [
            'locationData' => [
                ...$this->getFlattenedData($this->getLocationDataFull($place->getLatitude(), $place->getLongitude(), $placesNear, $place)), ...[
                    self::KEY_NAME_PLACES_NEAR => $placesNear,
                ]],
            'error' => null,
        ];
    }

    /**
     * Gets the location data as an array from given location/position.
     *
     * @return array{locationData: array<string, mixed>, error: string|null}
     * @throws Exception
     */
    #[ArrayShape([
        'locationData' => 'array',
        'error' => 'null|string',
    ])]
    public function getLocationDetailDataFromLocation(string $location): array
    {
        /* Detect position. */
        $position = GPSConverter::parseFullLocation2DecimalDegrees($location);

        /* Unable to find place with given position/location. */
        if ($position === false) {
            return [
                'locationData' => [],
                'error' => $this->translator->trans('general.notAvailableLocation', [
                    '%%location%' => $this->searchConfig->getLocationString(),
                ], 'location'),
            ];
        }

        /* Split position. */
        [$latitude, $longitude] = $position;

        $placesNear = [];

        return [
            'locationData' => [
                ...$this->getFlattenedData($this->getLocationDataFull($latitude, $longitude, $placesNear)), ...[
                    self::KEY_NAME_PLACES_NEAR => $placesNear,
                ]],
            'error' => null,
        ];
    }

    /**
     * Adds additional information to given places.
     *
     * @param Place[] $placeResults
     * @throws Exception
     */
    public function addAdditionalInformationToPlaces(array &$placeResults = [], bool $withAdminPlaces = false): void
    {
        /* No places given. */
        if (count($placeResults) <= 0) {
            return;
        }

        $search = $this->searchConfig->getSearchQuery();
        $location = $this->searchConfig->getLocationString();
        $sort = $this->searchConfig->getSort();
        $page = $this->searchConfig->getPage();

        /* Add distance. */
        if ($location !== null) {
            $locationSplit = preg_split('~,~', $location);

            if ($locationSplit === false) {
                throw new Exception(sprintf('Unable to split string (%s:%d).', __FILE__, __LINE__));
            }

            [$latitude, $longitude] = $locationSplit;

            foreach ($placeResults as $placeResult) {
                $distanceMeter = self::getDistanceBetweenTwoPointsInMeter(
                    floatval($latitude),
                    floatval($longitude),
                    $placeResult->getLatitude(),
                    $placeResult->getLongitude()
                );

                $placeResult->setDistanceMeter($distanceMeter);
            }
        }

        /* Add relevance. */
        foreach ($placeResults as $placeSource) {
            $relevance = self::getRelevance($search !== null ? $search : '', $sort, $placeSource);
            $placeSource->setRelevance($relevance);
        }

        /* Sort by given $sort. */
        switch ($sort) {
            /* Sort by distance */
            case SearchConfig::ORDER_BY_LOCATION:
                usort($placeResults, fn (Place $a, Place $b) => $a->getDistanceMeter() > $b->getDistanceMeter() ? 1 : -1);
                break;
                /* Sort by name */
            case SearchConfig::ORDER_BY_NAME:
                usort($placeResults, fn (Place $a, Place $b) => $a->getName() > $b->getName() ? 1 : -1);
                break;
                /* Sort by relevance */
            case SearchConfig::ORDER_BY_RELEVANCE:
            case SearchConfig::ORDER_BY_RELEVANCE_LOCATION:
                usort($placeResults, fn (Place $a, Place $b) => $a->getRelevance() > $b->getRelevance() ? -1 : 1);
                break;
        }

        $placeResults = array_slice($placeResults, ($page - 1) * $this->searchConfig->getNumberPerPage(), $this->searchConfig->getNumberPerPage());

        /* Add administration information */
        foreach ($placeResults as $placeResult) {
            /* Get placeP entities from given latitude and longitude. */
            if ($withAdminPlaces && !$placeResult->isAdminPlace()) {
                $placesP = $this->placeLoaderService->getPlacesPFromPosition($placeResult->getLatitude(), $placeResult->getLongitude(), Code::FEATURE_CODES_P_ADMIN_PLACES, $placeResult, 3);
            } else {
                $placesP = null;
            }

            $this->placeLoaderService->addAdministrationInformationToPlace($placeResult, $placesP);
        }
    }

    /**
     * Gets the location list results.
     *
     * @return array{results: Place[], numberResults: int, error: string|null}
     * @throws Exception
     */
    #[ArrayShape([
        'results' => 'array',
        'numberResults' => 'int',
        'error' => 'null|string',
    ])]
    public function getLocationListResults(string $search, bool $withAdminPlaces = false): array
    {
        if ($this->searchConfig->getViewMode() !== SearchConfig::VIEW_MODE_LIST) {
            throw new Exception(sprintf('Unsupported view mode (%s:%d).', __FILE__, __LINE__));
        }

        /* Search places. */
        $placeResults = $this->getLocationsByName($search);

        /* No place was found. */
        if (count($placeResults) <= 0) {
            return [
                'results' => [],
                'numberResults' => 0,
                'error' => $this->translator->trans('general.notAvailable', [
                    '%place%' => $this->searchConfig->getSearchQuery(),
                ], 'location'),
            ];
        }

        /* Save number of results. */
        $numberResults = count($placeResults);

        /* Add additional information. */
        /* PERFORMANCE */
        $this->addAdditionalInformationToPlaces($placeResults, $withAdminPlaces);

        return [
            'results' => $placeResults,
            'numberResults' => $numberResults,
            'error' => null,
        ];
    }

    /**
     * Returns a single data value.
     *
     * @param array<string, string|mixed|null> $addValues
     * @return array<string, string|mixed|null>
     */
    #[ArrayShape([
        self::KEY_NAME_TITLE => 'string',
        self::KEY_NAME_FORMAT => 'string',
        self::KEY_NAME_UNIT => 'null|string',
        self::KEY_NAME_UNIT_BEFORE => 'null|string',
        self::KEY_NAME_VALUE => 'mixed',
        self::KEY_NAME_VALUE_FORMATTED => 'string',
    ])]
    protected function getData(string $title, mixed $value, string $format, ?string $unit, ?string $unitBefore = null, ?string $valueFormatted = null, array $addValues = null): array
    {
        $formatted = sprintf($format, strval($value));

        $data = [
            self::KEY_NAME_TITLE => $title,
            self::KEY_NAME_FORMAT => $format,
            self::KEY_NAME_UNIT => $unit,
            self::KEY_NAME_UNIT_BEFORE => $unitBefore,
            self::KEY_NAME_VALUE => $value,
            self::KEY_NAME_VALUE_FORMATTED => sprintf('%s%s%s', $unitBefore, $valueFormatted ?? $formatted, $unit),
        ];

        if ($addValues !== null) {
            $data = array_merge($data, $addValues);
        }

        return $data;
    }

    /**
     * Returns flattened data from given data (we only need value-formatted).
     *
     * @param array<string, array<string, mixed>> $data
     * @return array<string, mixed>
     */
    protected function getFlattenedData(array $data, string $field = self::KEY_NAME_VALUE_FORMATTED): array
    {
        $flattenedData = [];

        foreach ($data as $key => $value) {
            $flattenedData[$key] = $value[$field];
        }

        return $flattenedData;
    }
}
