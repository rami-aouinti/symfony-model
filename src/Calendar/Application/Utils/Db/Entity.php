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
use App\Platform\Transport\Exception\ClassUnexpectedException;
use App\Platform\Transport\Exception\ClassUnsupportedException;
use App\Platform\Transport\Exception\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
class Entity
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @template T of object
     * @param array<string, mixed> $criteria
     * @param class-string<T> $className
     * @return T|null
     * @throws ClassNotFoundException
     * @throws ClassUnsupportedException
     * @throws ClassUnexpectedException
     */
    public function getOneNull(array $criteria, string $className)
    {
        $em = $this->entityManager;

        $repository = $em->getRepository($className);

        $repositoryName = $this->getRepositoryClass($className);

        if (!$repository instanceof $repositoryName) {
            throw new ClassNotFoundException($repositoryName);
        }

        $entity = $repository->findOneBy($criteria);

        if ($entity === null) {
            return null;
        }

        if (!$entity instanceof $className) {
            throw new ClassUnexpectedException($entity::class, $className);
        }

        return $entity;
    }

    /**
     * @template T of object
     * @param array<string, mixed> $criteria
     * @param class-string<T> $className
     * @return T
     * @throws ClassNotFoundException
     * @throws ClassUnsupportedException
     * @throws EntityNotFoundException
     * @throws ClassUnexpectedException
     */
    public function getOne(array $criteria, string $className)
    {
        $entity = $this->getOneNull($criteria, $className);

        if ($entity === null) {
            throw new EntityNotFoundException($className);
        }

        return $entity;
    }

    /**
     * Saves the given entity.
     *
     * @template T of object
     * @param object $entity
     */
    public function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
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
    protected function getRepositoryClass(string $className): string
    {
        return match (true) {
            $className === Calendar::class => CalendarRepository::class,
            default => throw new ClassUnsupportedException($className),
        };
    }
}
