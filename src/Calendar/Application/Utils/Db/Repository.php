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

namespace App\Calendar\Application\Utils\Db;

use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Infrastructure\Repository\CalendarRepository;
use App\Platform\Transport\Exception\ClassNotFoundException;
use App\Platform\Transport\Exception\ClassUnsupportedException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
class Repository
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     * @throws ClassUnsupportedException
     * @throws ClassNotFoundException
     */
    public function getRepository(string $className)
    {
        $em = $this->entityManager;

        $entityName = $this->getEntityClass($className);

        /** @var T $repository */
        $repository = $em->getRepository($entityName);

        if (!$repository instanceof $className) {
            throw new ClassNotFoundException($className);
        }

        return $repository;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param class-string $className
     * @return class-string
     * @throws ClassUnsupportedException
     */
    protected function getEntityClass(string $className): string
    {
        return match (true) {
            $className === CalendarRepository::class => Calendar::class,
            default => throw new ClassUnsupportedException($className),
        };
    }
}
