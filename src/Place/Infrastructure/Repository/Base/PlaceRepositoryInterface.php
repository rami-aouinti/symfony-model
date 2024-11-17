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

namespace App\Place\Infrastructure\Repository\Base;

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

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-05-21)
 * @package App\Command
 */
interface PlaceRepositoryInterface
{
    /**
     * Returns one by.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return PlaceA|PlaceH|PlaceL|PlaceP|PlaceR|PlaceS|PlaceT|PlaceU|PlaceV|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null);

    /**
     * Returns many by.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @return Place[]
     */
    public function findBy(array $criteria, ?array $orderBy = null);

    public function getHighestGeonameId(): int;
}
