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

use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\User\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2021-12-30)
 * @package App\Calendar\Infrastructure\Repository
 * @extends ServiceEntityRepository<CalendarImage>
 *
 * @method CalendarImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarImage[]    findAll()
 * @method CalendarImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarImage::class);
    }

    /**
     * Find one by name field.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findOneByYearAndMonth(User $user, Calendar $calendar, int $year, int $month): ?CalendarImage
    {
        $result = $this->createQueryBuilder('ci')
            ->where('ci.user = :user')
            ->andWhere('ci.calendar = :calendar')
            ->andWhere('ci.year = :year')
            ->andWhere('ci.month = :month')
            ->setParameter('user', $user)
            ->setParameter('calendar', $calendar)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result instanceof CalendarImage) {
            return $result;
        }

        if ($result !== null) {
            throw new Exception(sprintf('Unsupported type (%s:%d).', __FILE__, __LINE__));
        }

        return null;
    }

    /**
     * Find one by calendar id.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function findOneByCalendarId(User $user, int $calendarId): CalendarImage
    {
        $result = $this->createQueryBuilder('ci')
            ->where('ci.user = :user')
            ->andWhere('ci.calendar = :calendar')
            ->andWhere('ci.year = :year')
            ->andWhere('ci.month = :month')
            ->setParameter('user', $user)
            ->setParameter('id', $calendarId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result instanceof CalendarImage) {
            throw new Exception(sprintf('Unsupported type (%s:%d).', __FILE__, __LINE__));
        }

        return $result;
    }

    /**
     * Find calendar images by given calendar.
     *
     * @return CalendarImage[]
     */
    public function findByCalendar(Calendar $calendar): array
    {
        return $this->findBy([
            'calendar' => $calendar,
        ], [
            'month' => 'ASC',
        ]);
    }
}
