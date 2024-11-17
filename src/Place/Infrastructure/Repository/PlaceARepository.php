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

namespace App\Place\Infrastructure\Repository;

use App\Place\Domain\Entity\Place;
use App\Place\Domain\Entity\PlaceA;
use App\Place\Domain\Entity\PlaceP;
use App\Place\Infrastructure\Repository\Base\PlaceRepositoryInterface;
use App\Platform\Application\Utils\Constants\Code;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-22)
 * @since 0.1.1 (2022-11-22) Add PHP Magic Number Detector (PHPMND).
 * @since 0.1.0 (2022-05-20) First version.
 * @package App\Command
 * @extends ServiceEntityRepository<PlaceA>
 */
class PlaceARepository extends ServiceEntityRepository implements PlaceRepositoryInterface
{
    final public const int TYPE_2 = 2;

    protected ?QueryBuilder $lastSQL = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaceA::class);
    }

    /**
     * Gets the last SQL query.
     *
     * @throws Exception
     */
    public function getLastSQL(): string
    {
        if ($this->lastSQL === null) {
            throw new Exception(sprintf('Unable to get lastSQL (%s:%d).', __FILE__, __LINE__));
        }

        $sql = strval($this->lastSQL->getQuery()->getSQL());

        /** @var Parameter $parameter */
        foreach ($this->lastSQL->getParameters() as $parameter) {
            $value = strval($parameter->getValue());

            if ($parameter->getType() === self::TYPE_2) {
                $value = sprintf('"%s"', $value);
            }

            if ($sql === null) {
                throw new Exception(sprintf('Unable to get SQL (%s:%d).', __FILE__, __LINE__));
            }

            $sql = preg_replace('~\?~', $value, (string)$sql, 1);
        }

        if ($sql === null) {
            throw new Exception(sprintf('Unable to get SQL (%s:%d).', __FILE__, __LINE__));
        }

        return str_replace([
            'SELECT ',
            'FROM ',
            'WHERE ',
            'AND ',
            ', ',
        ], [
            'SELECT' . "\n" . '    ',
            "\n" . 'FROM' . "\n" . '    ',
            "\n" . 'WHERE' . "\n" . '    ',
            'AND' . "\n" . '    ',
            ',' . "\n" . '    ',
        ], (string)$sql);
    }

    /**
     * Sets the last SQL query.
     */
    public function setLastSQL(QueryBuilder $lastSQL): self
    {
        $this->lastSQL = $lastSQL;

        return $this;
    }

    /**
     * Find city by given place.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findCityByPlaceP(PlaceP $place): ?PlaceA
    {
        $countryCode = $place->getCountryCode();

        $queryBuilder = $this->createQueryBuilder('a');

        /* Country code */
        $queryBuilder->andWhere('a.countryCode = :cc')
            ->setParameter('cc', $countryCode);

        $queryBuilder->andWhere('a.featureClass = :fc')
            ->setParameter('fc', Code::FEATURE_CLASS_A);

        switch ($countryCode) {
            case 'AT':
            case 'CH':
            case 'ES':
            case 'PL':
                $queryBuilder->andWhere('a.featureCode = :fco')
                    ->setParameter('fco', Code::FEATURE_CODE_A_ADM3);
                $queryBuilder->andWhere('a.admin3Code = :ac')
                    ->setParameter('ac', $place->getAdmin3Code());
                break;
                /* de, etc. */
            default:
                $queryBuilder->andWhere('a.featureCode = :fco')
                    ->setParameter('fco', Code::FEATURE_CODE_A_ADM4);
                $queryBuilder->andWhere('a.admin4Code = :ac')
                    ->setParameter('ac', $place->getAdmin4Code());
        }

        $this->setLastSQL($queryBuilder);

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($result === null || $result instanceof PlaceA) {
            return $result;
        }

        throw new Exception(sprintf('Unexpected place instance (!PlaceA) (%s:%d).', __FILE__, __LINE__));
    }

    /**
     * Find state by given city.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findStateByPlaceP(Place $place): ?PlaceA
    {
        $countryCode = $place->getCountryCode();

        $queryBuilder = $this->createQueryBuilder('a');

        /* Country code */
        $queryBuilder->andWhere('a.countryCode = :cc')
            ->setParameter('cc', $countryCode);

        $queryBuilder->andWhere('a.featureClass = :fc')
            ->setParameter('fc', Code::FEATURE_CLASS_A);

        switch ($countryCode) {
            /* de, etc. */
            default:
                $queryBuilder->andWhere('a.featureCode = :fco')
                    ->setParameter('fco', Code::FEATURE_CODE_A_ADM1);
                $queryBuilder->andWhere('a.admin1Code = :ac')
                    ->setParameter('ac', $place->getAdmin1Code());
        }

        $this->setLastSQL($queryBuilder);

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($result === null || $result instanceof PlaceA) {
            return $result;
        }

        throw new Exception(sprintf('Unexpected place instance (!PlaceA) (%s:%d).', __FILE__, __LINE__));
    }

    /**
     * Get highest geoname id.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getHighestGeonameId(): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('MAX(p.geonameId)');

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }
}
