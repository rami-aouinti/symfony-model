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

namespace App\Place\Application\Service\Entity;

use App\Calendar\Application\Config\SearchConfig;
use App\Place\Application\Service\LocationDataService;
use App\Place\Domain\Entity\Place;
use App\Place\Domain\Entity\PlaceA;
use App\Place\Domain\Entity\PlaceH;
use App\Place\Domain\Entity\PlaceL;
use App\Place\Domain\Entity\PlaceP;
use App\Place\Domain\Entity\PlaceR;
use App\Place\Domain\Entity\PlaceS;
use App\Place\Domain\Entity\PlaceT;
use App\Place\Domain\Entity\PlaceU;
use App\Place\Domain\Entity\PlaceV;
use App\Place\Infrastructure\DataType\Point;
use App\Platform\Application\Utils\Constants\Code;
use App\Platform\Application\Utils\Constants\Country;
use App\Platform\Application\Utils\GPSConverter;
use App\Platform\Application\Utils\StringConverter;
use App\Platform\Application\Utils\Timer;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-05-21)
 * @package App\Command
 */
class PlaceLoaderService
{
    /* H → lakes */
    final public const MAX_DISTANCE_LAKE_METER_TITLE_RURAL = 2000;
    final public const MAX_DISTANCE_LAKE_METER_LIST_RURAL = self::MAX_DISTANCE_LAKE_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_LAKE_METER_TITLE_CITY = 1600;
    final public const MAX_DISTANCE_LAKE_METER_LIST_CITY = self::MAX_DISTANCE_LAKE_METER_LIST_RURAL;

    /* L → parks,area */
    final public const MAX_DISTANCE_PARK_METER_TITLE_RURAL = 1000;
    final public const MAX_DISTANCE_PARK_METER_LIST_RURAL = self::MAX_DISTANCE_PARK_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_PARK_METER_TITLE_CITY = 800;
    final public const MAX_DISTANCE_PARK_METER_LIST_CITY = self::MAX_DISTANCE_PARK_METER_LIST_RURAL;

    /* P → city, village */
    final public const MAX_DISTANCE_PLACE_METER_TITLE_RURAL = 1000;
    final public const MAX_DISTANCE_PLACE_METER_LIST_RURAL = self::MAX_DISTANCE_PLACE_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_PLACE_METER_TITLE_CITY = 1000;
    final public const MAX_DISTANCE_PLACE_METER_LIST_CITY = self::MAX_DISTANCE_PLACE_METER_LIST_RURAL;

    /* S → spot, building, farm */
    final public const MAX_DISTANCE_SPOT_METER_TITLE_RURAL = 300;
    final public const MAX_DISTANCE_SPOT_METER_LIST_RURAL = self::MAX_DISTANCE_SPOT_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_SPOT_METER_TITLE_CITY = 300;
    final public const MAX_DISTANCE_SPOT_METER_LIST_CITY = self::MAX_DISTANCE_SPOT_METER_LIST_RURAL;

    /* T → mountain, hill, rock */
    final public const MAX_DISTANCE_MOUNTAIN_METER_TITLE_RURAL = 2000;
    final public const MAX_DISTANCE_MOUNTAIN_METER_LIST_RURAL = self::MAX_DISTANCE_MOUNTAIN_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_MOUNTAIN_METER_TITLE_CITY = 500;
    final public const MAX_DISTANCE_MOUNTAIN_METER_LIST_CITY = self::MAX_DISTANCE_MOUNTAIN_METER_LIST_RURAL;

    /* V → forest,heath */
    final public const MAX_DISTANCE_FOREST_METER_TITLE_RURAL = 2000;
    final public const MAX_DISTANCE_FOREST_METER_LIST_RURAL = self::MAX_DISTANCE_FOREST_METER_TITLE_RURAL * 5;
    final public const MAX_DISTANCE_FOREST_METER_TITLE_CITY = 500;
    final public const MAX_DISTANCE_FOREST_METER_LIST_CITY = self::MAX_DISTANCE_FOREST_METER_LIST_RURAL;

    final public const CITY_POPULATION = 2000;

    final public const MAX_NUMBER_PLACES = 10;

    final public const CONFIG_ENTITY_CODES = [
        [
            'tags' => ['brücke', 'brücken'],
            'featureClasses' => [Code::FEATURE_CLASS_S],
            'featureCodes' => [Code::FEATURE_CODE_S_BDG],
        ],
        [
            'tags' => ['hotel', 'hotels'],
            'featureClasses' => [Code::FEATURE_CLASS_S],
            'featureCodes' => [Code::FEATURE_CODE_S_HTL],
        ],
        [
            'tags' => ['insel', 'inseln'],
            'featureClasses' => [Code::FEATURE_CLASS_T],
            'featureCodes' => [Code::FEATURE_CODE_T_ISL, Code::FEATURE_CODE_T_ISLS],
        ],
        [
            'tags' => ['kirche', 'kirchen'],
            'featureClasses' => [Code::FEATURE_CLASS_S],
            'featureCodes' => [Code::FEATURE_CODE_S_CH],
        ],
        [
            'tags' => ['ort'],
            'featureClasses' => [Code::FEATURE_CLASS_P],
            'featureCodes' => [],
        ],
        [
            'tags' => ['schloss', 'schlösser', 'burg', 'burgen'],
            'featureClasses' => [Code::FEATURE_CLASS_S],
            'featureCodes' => [Code::FEATURE_CODE_S_CSTL],
        ],
    ];

    final public const FEATURE_CLASSES_ALL = [
        Code::FEATURE_CLASS_P,
        Code::FEATURE_CLASS_A,
        Code::FEATURE_CLASS_S,
        Code::FEATURE_CLASS_H,
        Code::FEATURE_CLASS_L,
        Code::FEATURE_CLASS_R,
        Code::FEATURE_CLASS_T,
        Code::FEATURE_CLASS_U,
        Code::FEATURE_CLASS_V,
    ];

    protected const string RAW_SQL_POSITION = <<<SQL
SELECT
  p.*,
  ST_X(p.`coordinate`) AS latitude,
  ST_Y(p.`coordinate`) AS longitude,
  ST_DISTANCE(p.`coordinate`, GeomFromText('POINT(%f %f)')) AS distance
FROM %s p
WHERE p.`feature_class` = '%s'%s%s%s%s%s%s
ORDER BY distance ASC
LIMIT %d;
SQL;
    protected bool $debug = false;
    protected bool $verbose = false;

    public function __construct(
        private readonly EntityManagerInterface $em,
        protected TranslatorInterface $translator,
        protected ?\App\Place\Infrastructure\Repository\PlaceARepository $placeARepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceHRepository $placeHRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceLRepository $placeLRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlacePRepository $placePRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceRRepository $placeRRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceSRepository $placeSRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceTRepository $placeTRepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceURepository $placeURepository = null,
        protected ?\App\Place\Infrastructure\Repository\PlaceVRepository $placeVRepository = null
    ) {
    }

    /**
     * Set debug mode.
     *
     * @return $this
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Set verbose mode.
     *
     * @return $this
     */
    public function setVerbose(bool $verbose): self
    {
        $this->verbose = $verbose;

        return $this;
    }

    /**
     * Return new place instance.
     *
     * @throws Exception
     */
    public static function getPlace(string $featureClass): PlaceS|PlaceL|PlaceP|PlaceT|PlaceH|PlaceR|PlaceV|PlaceU|PlaceA
    {
        return match ($featureClass) {
            Code::FEATURE_CLASS_A => new PlaceA(),
            Code::FEATURE_CLASS_H => new PlaceH(),
            Code::FEATURE_CLASS_L => new PlaceL(),
            Code::FEATURE_CLASS_P => new PlaceP(),
            Code::FEATURE_CLASS_R => new PlaceR(),
            Code::FEATURE_CLASS_S => new PlaceS(),
            Code::FEATURE_CLASS_T => new PlaceT(),
            Code::FEATURE_CLASS_U => new PlaceU(),
            Code::FEATURE_CLASS_V => new PlaceV(),
            default => throw new Exception(sprintf('Unsupported feature class "%s" (%s:%d).', $featureClass, __FILE__, __LINE__)),
        };
    }

    /**
     * Find city from places (where population > 0).
     *
     * @param PlaceP[] $places
     */
    public function findCityByPlacesP(array $places): ?PlaceP
    {
        foreach ($places as $place) {
            if ($place->getPopulation() > 0) {
                return $place;
            }
        }

        return null;
    }

    /**
     * Returns country name by given place.
     */
    public function getCountryByPlace(Place $place): string
    {
        $countryCode = strtolower($place->getCountryCode());

        return $this->translator->trans(sprintf('country.alpha2.%s', $countryCode), [], 'countries', $countryCode);
    }

    /**
     * Returns another PlaceP entry, if given entry has no population.
     *
     * @param PlaceP[] $placesP
     */
    public function getCityPWithPopulationFromPlacesP(PlaceP $placeP, array $placesP): ?PlaceP
    {
        if ($placeP->getPopulation(true) > 0) {
            return null;
        }

        $cityP = $this->findCityByPlacesP($placesP);

        if ($cityP !== null) {
            $placeP->setCityP($cityP);
            if ($cityP->getPopulation() !== null) {
                $placeP->setPopulation($cityP->getPopulation());
            }
        }

        return $cityP;
    }

    /**
     * Gets a PlaceA entry (city) from Place P
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function getCityByPlacePFromAdmin(PlaceP $placeP): ?PlaceA
    {
        if ($this->placeARepository === null) {
            return null;
        }

        $cityATimer = Timer::start();
        $cityA = $this->placeARepository->findCityByPlaceP($placeP);
        $cityATime = Timer::stop($cityATimer);
        $this->printRawQuery('cityA', $this->placeARepository->getLastSQL(), $cityATime);

        return $cityA;
    }

    /**
     * Gets the state from place.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function getStateFromPlace(Place|null $place): ?PlaceA
    {
        if ($place === null) {
            return null;
        }

        if ($this->placeARepository === null) {
            return null;
        }

        $stateTimer = Timer::start();
        $state = $this->placeARepository->findStateByPlaceP($place);
        $stateTime = Timer::stop($stateTimer);
        $this->printRawQuery('state', $this->placeARepository->getLastSQL(), $stateTime);

        return $state;
    }

    /**
     * Adds administration information to given place.
     *
     * @param PlaceP[] $placesP
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function addAdministrationInformationToPlace(Place $place, ?array $placesP = null, ?Place $placeSource = null): void
    {
        $city = null;
        $district = null;
        switch (true) {
            /* PlaceP: Add administrative information */
            case in_array($place->getFeatureCode(), Code::FEATURE_CODES_P_DISTRICT_PLACES) && $placesP !== null:
                if (!$place instanceof PlaceP) {
                    throw new Exception(sprintf('Unexpected type of Place (%s:%d).', __FILE__, __LINE__));
                }

                $district = $place;

                $city1 = $this->getCityByPlacePFromAdmin($district);
                $city2 = $this->findNextAdminCity($placesP, $district);
                $city3 = $this->findNextCityPopulation($placesP, $district);

                $city = match (true) {
                    $city1 === null => $city2 !== null && $city2->getPopulation(true) > 0 ? $city2 : $city3,
                    // no break
                    default => $city1,
                };

                break;
                /* PlaceP: Add administrative information (Admin place) */
            case in_array($place->getFeatureCode(), Code::FEATURE_CODES_P_ADMIN_PLACES) && $placesP !== null:
                if (!$place instanceof PlaceP) {
                    throw new Exception(sprintf('Unexpected type of Place (%s:%d).', __FILE__, __LINE__));
                }

                $city = $place;
                $district = $this->findNextDistrict($placesP, $city);
                break;
                /* Places P given. Extract city from first placeP entry. */
            case $placesP !== null:
                $placeAdmin = array_shift($placesP);

                if ($placeAdmin === null) {
                    throw new Exception(sprintf('Unable to find first placeP entry (%s:%d).', __FILE__, __LINE__));
                }

                $this->addAdministrationInformationToPlace($placeAdmin, $placesP);

                $city = $placeAdmin->getCity();
                $district = $placeAdmin->getDistrict();
                break;
        }

        /* Disable district. */
        if ($district !== null && $city !== null && $district->getName() === $city->getName()) {
            $district = null;
        }

        $state = $this->getStateFromPlace($place);
        $country = $this->getCountryByPlace($place);

        switch (true) {
            case $placeSource !== null && $placeSource->getName() === $country:
                $place->setDistrict(null);
                $place->setCity(null);
                $place->setState(null);
                break;
            case $placeSource !== null && $state !== null && $placeSource->getName() === $state->getName():
                $place->setDistrict(null);
                $place->setCity(null);
                $place->setState($state);
                break;
            case $placeSource !== null && $city !== null && $placeSource->getName() === $city->getName():
                $place->setDistrict(null);
                $place->setCity($city);
                $place->setState($state);
                break;
            default:
                $place->setDistrict($district);
                $place->setCity($city);
                $place->setState($state);
                break;
        }
        $place->setCountry($country);

        if ($city !== null) {
            $place->setIsCity($city->getPopulation(true) > self::CITY_POPULATION);
            $place->setPopulationAdmin($city->getPopulation(true));
        } else {
            $place->setIsCity(false);
            $place->setPopulationAdmin(0);
        }
    }

    /**
     * Returns PlaceP entities from given latitude and longitude.
     *
     * @param string|string[]|null $featureCodes
     * @return PlaceP[]|null
     * @throws DoctrineDBALException
     * @throws Exception
     */
    public function getPlacesPFromPosition(float $latitude, float $longitude, string|array|null $featureCodes = null, ?Place $placeSource = null, int $limit = 50): ?array
    {
        $connection = $this->getEntityManager()->getConnection();
        $featureClass = Code::FEATURE_CLASS_P;

        /* Find feature class P */
        $cityPTimer = Timer::start();
        $sqlRaw = $this->getRawSqlPosition($latitude, $longitude, $limit, $featureClass, $featureCodes);

        $statement = $connection->prepare($sqlRaw);
        $result = $statement->executeQuery();
        $cityPTime = Timer::stop($cityPTimer);
        $this->printRawQuery('places', $sqlRaw, $cityPTime);

        /* Reads all results. */
        /** @var PlaceP[] $placesP */
        $placesP = [];
        while (($row = $result->fetchAssociative()) !== false) {
            /* Build and add place. */
            $placeP = $this->buildPlaceFromRow($row, $latitude, $longitude, $featureClass, $placeSource);

            if (!$placeP instanceof PlaceP) {
                throw new Exception(sprintf('Unexpected place instance "%s" (%s:%d).', $placeP::class, __FILE__, __LINE__));
            }

            $placesP[] = $placeP;
        }

        /* No result was found. */
        if (count($placesP) === 0) {
            return null;
        }

        /* Sort placesP */
        usort($placesP, fn (PlaceP $a, PlaceP $b) => $a->getDistanceMeter() > $b->getDistanceMeter() ? 1 : -1);

        return $placesP;
    }

    /**
     * Finds the nearest place by coordinate.
     *
     * @param string|string[]|null $featureCodes
     * @param array<string, Place[]> $data
     * @throws DoctrineDBALException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findPlaceByPositionOrPlaceSource(float $latitude, float $longitude, string|array|null $featureCodes = null, array &$data = [], ?Place $placeSource = null): ?Place
    {
        if ($this->placeARepository === null) {
            return null;
        }

        /* Get placeP entities from given latitude and longitude. */
        $placesP = $this->getPlacesPFromPosition($latitude, $longitude, $featureCodes, $placeSource);

        /* No placeP entries was found. */
        if ($placesP === null) {
            throw new Exception(sprintf('No placeP entries found (%s:%d).', __FILE__, __LINE__));
        }

        /* Gets first placeP entity or given placeSource. */
        if ($placeSource !== null) {
            $place = $placeSource;
        } else {
            $place = array_shift($placesP);
        }

        if ($place === null) {
            throw new Exception(sprintf('Unable to get placeP entity (%s:%d).', __FILE__, __LINE__));
        }

        $place = $this->addPlacesToPlace($place, $placesP, PlaceP::class);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['P'] = $place->getPlaces(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* Verbose information */
        $this->printPlaces($placesP, $latitude, $longitude);

        /* Add district, city, etc. */
        $this->addAdministrationInformationToPlace($place, $placesP, $placeSource);

        /* H → Lakes */
        $lakePlaces = $this->findByPosition($latitude, $longitude, 50, Code::FEATURE_CLASS_H);
        $place = $this->addPlacesToPlace($place, $lakePlaces, PlaceH::class, true);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['H'] = $place->getLakes(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* L → Parks, Areas */
        $parkPlaces = $this->findByPosition($latitude, $longitude, 50, Code::FEATURE_CLASS_L);
        $place = $this->addPlacesToPlace($place, $parkPlaces, PlaceL::class, true);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['L'] = $place->getParks(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* S → Add point of interest (Hotel, Rail station) */
        $spotPlaces = $this->findByPosition($latitude, $longitude, 50, Code::FEATURE_CLASS_S);
        $place = $this->addPlacesToPlace($place, $spotPlaces, PlaceS::class, true);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['S'] = $place->getSpots(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* T → Mountain */
        $mountainPlaces = $this->findByPosition($latitude, $longitude, 50, Code::FEATURE_CLASS_T);
        $place = $this->addPlacesToPlace($place, $mountainPlaces, PlaceT::class, true);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['T'] = $place->getMountains(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* V → Forest */
        $forestPlaces = $this->findByPosition($latitude, $longitude, 50, Code::FEATURE_CLASS_V);
        $place = $this->addPlacesToPlace($place, $forestPlaces, PlaceV::class, true);
        /** @phpstan-ignore-next-line → PHPStan: $placeSource is not nullsafe */
        $data['V'] = $place->getForests(self::MAX_NUMBER_PLACES, $placeSource?->getId());

        /* Verbose information */
        $this->printPlaceInformation($place);

        return $place;
    }

    /**
     * Finds place by id.
     *
     * @throws Exception
     */
    public function findById(int $id, string $code): ?Place
    {
        $place = $this->em->getRepository($this->getEntityClass($code))
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($place === null) {
            return null;
        }

        if (!$place instanceof Place) {
            throw new Exception(sprintf('Unsupported result (%s:%d).', __FILE__, __LINE__));
        }

        return $place;
    }

    /**
     * Finds place by code:id.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findByCodeId(string $codeId): ?Place
    {
        $matches = [];
        if (!preg_match('~^([ahlprstuv]):(\d+)$~', $codeId, $matches)) {
            throw new Exception(sprintf('Unsupported format "%s" (%s:%d).', $codeId, __FILE__, __LINE__));
        }

        $code = strtoupper(strval($matches[1]));
        $id = strval($matches[2]);

        $place = $this->em->getRepository($this->getEntityClass($code))
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($place === null) {
            return null;
        }

        if (!$place instanceof Place) {
            throw new Exception(sprintf('Unsupported result (%s:%d).', __FILE__, __LINE__));
        }

        return $place;
    }

    /**
     * Finds places by name.
     *
     * @return Place[]
     * @throws Exception
     */
    public function findByName(string $name): array
    {
        $places = [];
        $entityCodes = $this->getEntityCodes($name);

        $featureClasses = $entityCodes['featureClasses'];
        $featureCodes = $entityCodes['featureCodes'];
        $names = $entityCodes['names'];

        foreach ($featureClasses as $featureClass) {
            $queryBuilder = $this->em->getRepository($this->getEntityClass($featureClass))
                ->createQueryBuilder('p');

            $queryBuilder->where('p.id > 0');

            if (count($featureCodes) > 0) {
                $queryBuilder
                    ->andWhere('p.featureCode IN(:featureCodes)')
                    ->setParameter('featureCodes', $featureCodes);
            }

            foreach ($names as $number => $name) {
                $nameParameter = sprintf('name%d', $number);

                $queryBuilder
                    ->andWhere(sprintf('p.name LIKE :%s', $nameParameter))
                    ->setParameter($nameParameter, sprintf('%%%s%%', $name));
            }

            $queryBuilder
                ->orderBy('p.population', Criteria::DESC);

            /** @var Place[] $records */
            $records = $queryBuilder->getQuery()->getResult();

            foreach ($records as $place) {
                $places[] = $place;
            }
        }

        if (count($places) > 0) {
            /* Sort placesP */
            usort($places, fn (Place $a, Place $b) => $a->getPopulation() < $b->getPopulation() ? 1 : -1);

            return $places;
        }

        return $places;
    }

    /**
     * Finds the nearest place by coordinate.
     *
     * @param string|string[]|null $featureCodes
     * @return PlaceA[]|PlaceH[]|PlaceL[]|PlaceP[]|PlaceR[]|PlaceS[]|PlaceT[]|PlaceU[]|PlaceV[]
     * @throws DoctrineDBALException
     * @throws Exception
     */
    public function findByPosition(float $latitude, float $longitude, int $limit = 1, string $featureClass = Code::FEATURE_CLASS_P, string|array|null $featureCodes = null, ?string $countryCode = null, ?string $adminCode3 = null, ?string $adminCode4 = null): array
    {
        $places = [];

        $connection = $this->getEntityManager()->getConnection();

        $positionTimer = Timer::start();
        $sqlRaw = $this->getRawSqlPosition($latitude, $longitude, $limit, $featureClass, $featureCodes, $countryCode, null, null, $adminCode3, $adminCode4);
        $statement = $connection->prepare($sqlRaw);
        $result = $statement->executeQuery();
        $positionTime = Timer::stop($positionTimer);
        $this->printRawQuery(sprintf('Place%s', $featureClass), $sqlRaw, $positionTime);

        while (($row = $result->fetchAssociative()) !== false) {
            /* Build place. */
            $place = $this->buildPlaceFromRow($row, $latitude, $longitude, $featureClass);

            $place->setName(ucfirst($place->getName()));

            $places[] = $place;
        }

        /* Sort placesP */
        usort($places, fn (Place $a, Place $b) => $a->getDistanceMeter() > $b->getDistanceMeter() ? 1 : -1);

        return $places;
    }

    /**
     * Returns the entity manager.
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * Checks that all needles are available within given haystack.
     *
     * @param string[] $needles
     * @param string[] $haystack
     */
    protected function inArray(?array $needles, array $haystack): bool
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if (!in_array($needle, $haystack)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Builds place from given row.
     *
     * @param array<string,mixed> $row
     * @throws Exception
     */
    protected function buildPlaceFromRow(array $row, float $latitude, float $longitude, string $featureClass = Code::FEATURE_CLASS_A, ?Place $placeSource = null, string $sortBy = SearchConfig::ORDER_BY_RELEVANCE): PlaceA|PlaceH|PlaceL|PlaceP|PlaceR|PlaceS|PlaceT|PlaceU|PlaceV
    {
        $place = self::getPlace($featureClass);

        $place->setIdTmp(intval($row['id']));
        $place->setGeonameId(intval($row['geoname_id']));
        $place->setName(strval($row['name']));
        $place->setAsciiName(strval($row['ascii_name']));
        $place->setAlternateNames(strval($row['alternate_names']));
        $place->setCoordinate(new Point(floatval($row['latitude']), floatval($row['longitude'])));
        $place->setFeatureClass(strval($row['feature_class']));
        $place->setFeatureCode(strval($row['feature_code']));
        $place->setCountryCode(strval($row['country_code']));
        $place->setCc2(strval($row['cc2']));
        $place->setPopulation(strval($row['population']));
        $place->setElevation(intval($row['elevation']));
        $place->setDem(!empty($row['dem']) ? intval($row['dem']) : null);
        $place->setTimezone(strval($row['timezone']));
        $place->setModificationDate(StringConverter::convertDateTime(strval($row['modification_date'])));
        $place->setCreatedAt(DateTimeImmutable::createFromMutable(StringConverter::convertDateTime(strval($row['created_at']))));
        $place->setUpdatedAt(DateTimeImmutable::createFromMutable(StringConverter::convertDateTime(strval($row['updated_at']))));
        $place->setDistanceDb(floatval($row['distance']));
        $place->setDistanceMeter(LocationDataService::getDistanceBetweenTwoPointsInMeter($latitude, $longitude, floatval($row['latitude']), floatval($row['longitude'])));
        $place->setDirection(GPSConverter::getDirectionFromPositions($latitude, $longitude, floatval($row['latitude']), floatval($row['longitude'])));
        $place->setDegree(GPSConverter::getDegree($latitude, $longitude, floatval($row['latitude']), floatval($row['longitude'])));
        $place->setAdmin1Code(!empty($row['admin1_code']) ? strval($row['admin1_code']) : null);
        $place->setAdmin2Code(!empty($row['admin2_code']) ? strval($row['admin2_code']) : null);
        $place->setAdmin3Code(!empty($row['admin3_code']) ? strval($row['admin3_code']) : null);
        $place->setAdmin4Code(!empty($row['admin4_code']) ? strval($row['admin4_code']) : null);
        $place->setRelevance(LocationDataService::getRelevance(strval($row['name']), $sortBy, $placeSource));

        return $place;
    }

    /**
     * Builds raw sql query for position requests.
     *
     * @param string|string[]|null $featureCodes
     * @throws Exception
     */
    protected function getRawSqlPosition(
        float $latitude,
        float $longitude,
        int $limit = 1,
        string $featureClass = Code::FEATURE_CLASS_P,
        string|array|null $featureCodes = null,
        ?string $countryCode = null,
        ?string $adminCode1 = null,
        ?string $adminCode2 = null,
        ?string $adminCode3 = null,
        ?string $adminCode4 = null
    ): string {
        if (is_string($featureCodes)) {
            $featureCodes = [$featureCodes];
        }

        if (!array_key_exists($featureClass, Code::FEATURE_CODES_ALL)) {
            throw new Exception(sprintf('Unable to find feature class set with feature code "%s" (%s:%d).', $featureClass, __FILE__, __LINE__));
        }

        $featureCodesAll = Code::FEATURE_CODES_ALL[$featureClass];

        /* @see http://www.geonames.org/export/codes.html */
        if ($this->inArray($featureCodes, $featureCodesAll)) {
            $sqlWhereFeatureCode = sprintf(' AND p.`feature_code` IN (\'%s\')', implode('\', \'', $featureCodes));
        } else {
            $sqlWhereFeatureCode = '';
        }

        if ($countryCode !== null) {
            $sqlWhereCountryCode = sprintf(' AND p.`country_code` = \'%s\'', $countryCode);
        } else {
            $sqlWhereCountryCode = '';
        }

        if ($adminCode1 !== null) {
            $sqlWhereAdmin1Code = sprintf(' AND p.`admin1_code` = \'%s\'', $adminCode1);
        } else {
            $sqlWhereAdmin1Code = '';
        }

        if ($adminCode2 !== null) {
            $sqlWhereAdmin2Code = sprintf(' AND p.`admin2_code` = \'%s\'', $adminCode2);
        } else {
            $sqlWhereAdmin2Code = '';
        }

        if ($adminCode3 !== null) {
            $sqlWhereAdmin3Code = sprintf(' AND p.`admin3_code` = \'%s\'', $adminCode3);
        } else {
            $sqlWhereAdmin3Code = '';
        }

        if ($adminCode4 !== null) {
            $sqlWhereAdmin4Code = sprintf(' AND p.`admin4_code` = \'%s\'', $adminCode4);
        } else {
            $sqlWhereAdmin4Code = '';
        }

        $tableName = sprintf('place_%s', strtolower($featureClass));

        return sprintf(
            self::RAW_SQL_POSITION,
            $latitude,
            $longitude,
            $tableName,
            $featureClass,
            $sqlWhereFeatureCode,
            $sqlWhereCountryCode,
            $sqlWhereAdmin1Code,
            $sqlWhereAdmin2Code,
            $sqlWhereAdmin3Code,
            $sqlWhereAdmin4Code,
            $limit
        );
    }

    /**
     * Returns raw query to get a PlaceA object ordered by location.
     *
     * @throws Exception
     */
    protected function getRawQueryPlaceAFromPlaceP(float $latitude, float $longitude, PlaceP $placeP): string
    {
        return match ($placeP->getCountryCode()) {
            Country::AUSTRIA_ISO_2, Country::SWITZERLAND_ISO_2, Country::SPAIN_ISO_2, Country::POLAND_ISO_2 => $this->getRawSqlPosition($latitude, $longitude, 1, Code::FEATURE_CLASS_A, Code::FEATURE_CODE_A_ADM3, $placeP->getCountry(), null, null, $placeP->getAdmin3Code(), $placeP->getAdmin4Code()),
            default => $this->getRawSqlPosition($latitude, $longitude, 1, Code::FEATURE_CLASS_A, Code::FEATURE_CODE_A_ADM4, $placeP->getCountry(), null, null, null, $placeP->getAdmin4Code()),
        };
    }

    /**
     * Finds next admin place.
     *
     * @param PlaceP[] $placesP
     */
    protected function findNextAdminCity(array $placesP, Place $place): ?PlaceP
    {
        /* Get the nearest placeP entry with equal admin4 code. */
        foreach ($placesP as $placeP) {
            switch (true) {
                case in_array($placeP->getFeatureCode(), Code::FEATURE_CODES_P_ADMIN_PLACES) &&
                $place->getAdmin4Code() == $placeP->getAdmin4Code():
                    return $placeP;
            }
        }

        /* Get the nearest placeP entry if no equal admin4 code was found. */
        foreach ($placesP as $placeP) {
            switch (true) {
                case in_array($placeP->getFeatureCode(), Code::FEATURE_CODES_P_ADMIN_PLACES):
                    #print $placeP->getName();
                    #exit();

                    return $placeP;
            }
        }

        return null;
    }

    /**
     * Finds next place with more than 0 population.
     *
     * @param PlaceP[] $placesP
     */
    protected function findNextCityPopulation(array $placesP, Place $place): ?PlaceP
    {
        if ($place->getPopulation(true) > 0) {
            return null;
        }

        foreach ($placesP as $placeP) {
            switch (true) {
                case $placeP->getPopulation(true) > 0 && $place->getAdmin4Code() == $placeP->getAdmin4Code():
                    $place->setPopulation($placeP->getPopulation());

                    return $placeP;
            }
        }

        return null;
    }

    /**
     * Finds next admin place.
     *
     * @param PlaceP[] $placesP
     */
    protected function findNextDistrict(array $placesP, PlaceP $city): ?PlaceP
    {
        foreach ($placesP as $placeP) {
            switch (true) {
                case in_array($placeP->getFeatureCode(), Code::FEATURE_CODES_P_DISTRICT_PLACES) && $city->getAdmin4Code() == $placeP->getAdmin4Code():
                    return $placeP;
            }
        }

        return null;
    }

    /**
     * Translate given place. Add feature name to name.
     */
    protected function translateFeatureCode(Place $place): void
    {
        $translate = false;

        switch (true) {
            case $place->getFeatureClass() === Code::FEATURE_CLASS_L && $place->getFeatureCode() === Code::FEATURE_CODE_L_PRK:
            case $place->getFeatureClass() === Code::FEATURE_CLASS_S && $place->getFeatureCode() === Code::FEATURE_CODE_S_RSTN:
            case $place->getFeatureClass() === Code::FEATURE_CLASS_T && $place->getFeatureCode() === Code::FEATURE_CODE_T_BCH:
                $translate = true;
                break;
        }

        if ($translate) {
            $value = $this->translator->trans(sprintf('%s.%s', $place->getFeatureClass(), $place->getFeatureCode()), [], 'place', strtolower($place->getCountryCode()));
            $name = $place->getName();

            if (!str_contains(strtolower($name), strtolower($value))) {
                $place->setName(sprintf('%s %s', $value, $name));
            }
        }
    }

    /**
     * Add given places to place.
     *
     * @param Place[] $places
     * @throws Exception
     */
    protected function addPlacesToPlace(Place $place, array $places, string $className, bool $sort = false): Place
    {
        /* Sort places */
        if ($sort) {
            usort($places, fn (Place $a, Place $b) => $a->getDistanceMeter() > $b->getDistanceMeter() ? 1 : -1);
        }

        foreach ($places as $placeEntry) {
            if ($placeEntry::class !== $className) {
                throw new Exception(sprintf('Unexpected place instance "%s". "%s" expected (%s:%d).', $placeEntry::class, $className, __FILE__, __LINE__));
            }

            $this->translateFeatureCode($placeEntry);

            match (true) {
                $placeEntry instanceof PlaceH => $place->addLake($placeEntry),
                $placeEntry instanceof PlaceL => $place->addPark($placeEntry),
                $placeEntry instanceof PlaceP => $place->addPlace($placeEntry),
                $placeEntry instanceof PlaceS => $place->addSpot($placeEntry),
                $placeEntry instanceof PlaceT => $place->addMountain($placeEntry),
                $placeEntry instanceof PlaceV => $place->addForest($placeEntry),
                default => throw new Exception(sprintf('Unsupported class "%s" (%s:%d).', $placeEntry::class, __FILE__, __LINE__)),
            };
        }

        return $place;
    }

    /**
     * Gets the entity class.
     *
     * @return class-string<PlaceA>|class-string<PlaceH>|class-string<PlaceL>|class-string<PlaceP>|class-string<PlaceR>|class-string<PlaceS>|class-string<PlaceT>|class-string<PlaceU>|class-string<PlaceV>
     * @throws Exception
     */
    protected function getEntityClass(string $code): string
    {
        return match ($code) {
            Code::FEATURE_CLASS_A => PlaceA::class,
            Code::FEATURE_CLASS_H => PlaceH::class,
            Code::FEATURE_CLASS_L => PlaceL::class,
            Code::FEATURE_CLASS_P => PlaceP::class,
            Code::FEATURE_CLASS_R => PlaceR::class,
            Code::FEATURE_CLASS_S => PlaceS::class,
            Code::FEATURE_CLASS_T => PlaceT::class,
            Code::FEATURE_CLASS_U => PlaceU::class,
            Code::FEATURE_CLASS_V => PlaceV::class,
            default => throw new Exception(sprintf('Unexpected code given "%s (%s:%d).', $code, __FILE__, __LINE__)),
        };
    }

    /**
     * Trim function.
     *
     * @throws Exception
     */
    protected function trim(string $string): string
    {
        $string = trim($string);

        $string = preg_replace('~[ ]{2,}~', ' ', $string);

        if (!is_string($string)) {
            throw new Exception(sprintf('Unable to replace given string "%s (%s:%d).', $string, __FILE__, __LINE__));
        }

        return $string;
    }

    /**
     * Prepares given search string.
     *
     * @throws Exception
     */
    protected function prepareSearchString(string $search): string
    {
        /* Remove "," from search. */
        $words = preg_split('~[ ,]+~', $search);
        if ($words === false) {
            throw new Exception(sprintf('Unable to split given name "%s (%s:%d).', $search, __FILE__, __LINE__));
        }

        return implode(' ', $words);
    }

    /**
     * Get entity codes (feature classes and feature codes) according to given name.
     *
     * @return array<string, array<string>>
     * @throws Exception
     */
    protected function getEntityCodes(string &$name): array
    {
        $name = $this->prepareSearchString($name);

        foreach (self::CONFIG_ENTITY_CODES as $configEntityCodes) {
            $tags = implode('|', $configEntityCodes['tags']);
            $regexp = sprintf('~(^| )(%s)( |$)~i', $tags);

            if (preg_match($regexp, $name)) {
                $name = preg_replace($regexp, '$1$3', $name);

                if (!is_string($name)) {
                    throw new Exception(sprintf('Unable to replace given name (%s:%d).', __FILE__, __LINE__));
                }

                $name = $this->trim($name);

                return [
                    'featureClasses' => $configEntityCodes['featureClasses'],
                    'featureCodes' => $configEntityCodes['featureCodes'],
                    'names' => !empty($name) ? explode(' ', $name) : [],
                ];
            }
        }

        return [
            'featureClasses' => self::FEATURE_CLASSES_ALL,
            'featureCodes' => [],
            'names' => !empty($name) ? explode(' ', $name) : [],
        ];
    }

    /**
     * Use sprintf in a utf8 way.
     *
     * @param mixed ...$args
     * @throws Exception
     */
    protected function mbSprintf(string $format, ...$args): string
    {
        $params = $args;

        $callback = function ($length) use (&$params) {
            $value = array_shift($params);

            $value = match (true) {
                $value === null => '',
                default => strval($value),
            };

            return strlen($value) - mb_strlen($value) + $length[0];
        };

        /** @phpstan-ignore-next-line → PHPStan does not detect $callback as valid */
        $format = preg_replace_callback('/(?<=%|%-)\d+(?=s)/', $callback, $format);

        if ($format === null) {
            throw new Exception(sprintf('Unable to execute preg_replace_callback (%s:%d).', __FILE__, __LINE__));
        }

        foreach ($args as &$arg) {
            $arg = strval($arg);
        }

        /** @phpstan-ignore-next-line → All arguments are already converted into string. */
        return sprintf($format, ...$args);
    }

    /**
     * Prints raw query.
     */
    protected function printRawQuery(string $name, string $sqlRaw, float $time): void
    {
        if (!$this->debug) {
            return;
        }

        $title = sprintf('SQL "%s" - %.4fs', $name, $time);

        print $title . "\n";
        print str_repeat('-', strlen($title)) . "\n";
        print $sqlRaw . "\n";
        print str_repeat('-', strlen($title)) . "\n";
        print "\n";
    }

    /**
     * Prints next places.
     *
     * @param PlaceP[] $placesP
     * @throws Exception
     */
    protected function printPlaces(array $placesP, float $latitude, float $longitude): void
    {
        if (!$this->verbose) {
            return;
        }

        $title = sprintf('Next 50 places (%.4f° %.4f°)', $latitude, $longitude);
        $format = '%-42s %-6s %6s %6s %6s %10s %10s %10s %10s %12s';

        print "\n";
        print $title . "\n";
        print str_repeat('-', strlen($title)) . "\n";

        $caption = $this->mbSprintf($format, 'Name', 'FC', 'ADM1', 'ADM2', 'ADM3', 'ADM4', 'Population', 'Latitude', 'Longitude', 'Distance');

        print $caption . "\n";
        print str_repeat('-', strlen($caption)) . "\n";
        foreach ($placesP as $placeP) {
            /* TODO: Change latitude and longitude within db. It is reversed. */
            $distance = LocationDataService::getDistanceBetweenTwoPoints($latitude, $longitude, $placeP->getLatitude(), $placeP->getLongitude());

            print $this->mbSprintf(
                $format,
                $placeP->getName(),
                $placeP->getFeatureCode(),
                $placeP->getAdmin1Code(),
                $placeP->getAdmin2Code(),
                $placeP->getAdmin3Code(),
                $placeP->getAdmin4Code(),
                $placeP->getPopulation(),
                sprintf('%.4f°', $placeP->getLatitude()),
                sprintf('%.4f°', $placeP->getLongitude()),
                sprintf('%.1f m', $distance['meters'])
            ) . "\n";
        }

        print "\n";
    }

    /**
     * Prints district, city, state and country.
     *
     * @throws Exception
     */
    protected function printPlaceInformation(Place $place): void
    {
        if (!$this->verbose) {
            return;
        }

        $title = 'Place information';
        $format = '%-10s %-63s %-6s';
        $district = $place->getDistrict();
        $city = $place->getCity();
        $state = $place->getState();
        $country = $place->getCountry();

        $caption = $this->mbSprintf($format, '', 'Name', 'FC');

        print "\n";
        print $title . "\n";
        print str_repeat('-', strlen($title)) . "\n";
        print $caption . "\n";
        print str_repeat('-', strlen($caption)) . "\n";

        if ($district !== null) {
            print $this->mbSprintf($format, 'District', $district->getName(), $district->getFeatureCode()) . "\n";
        } else {
            print $this->mbSprintf($format, 'District', 'Kein Stadtteil gefunden.', '') . "\n";
        }

        if ($city !== null) {
            print $this->mbSprintf($format, 'City', $city->getName(), $city->getFeatureCode()) . "\n";
        } else {
            print $this->mbSprintf($format, 'City', 'Keine Stadt gefunden.', '') . "\n";
        }

        if ($state !== null) {
            print $this->mbSprintf($format, 'State', $state->getName(), $state->getFeatureCode()) . "\n";
        } else {
            print $this->mbSprintf($format, 'State', 'Keine Bundesland/Gemeinde gefunden.', '') . "\n";
        }

        if ($country !== null) {
            print $this->mbSprintf($format, 'Country', $country, '') . "\n";
        } else {
            print $this->mbSprintf($format, 'Country', 'Kein Land angegeben.', '') . "\n";
        }

        print "\n";
    }
}
