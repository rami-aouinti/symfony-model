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

namespace App\Calendar\Infrastructure\Repository;

use App\Calendar\Domain\Entity\CalendarStyle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2021-12-30)
 * @package App\Calendar\Infrastructure\Repository
 * @extends ServiceEntityRepository<CalendarStyle>
 *
 * @method CalendarStyle|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarStyle|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarStyle[]    findAll()
 * @method CalendarStyle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarStyleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarStyle::class);
    }
}
