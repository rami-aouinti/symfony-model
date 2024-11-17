<?php

declare(strict_types=1);

namespace App\Property\Application\Service\Cache;

use App\Category\Infrastructure\Repository\CategoryRepository;
use App\Place\Infrastructure\Repository\CityRepository;
use App\Platform\Infrastructure\Repository\PageRepository;
use App\Property\Infrastructure\Repository\DealTypeRepository;
use App\Property\Infrastructure\Repository\PropertyRepository;
use App\User\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 *
 */
trait GetCache
{
    private FilesystemAdapter $cache;
    private ManagerRegistry $doctrine;
    private string $persistentObjectName;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->cache = new FilesystemAdapter();
        $this->doctrine = $doctrine;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCount(string $key, string $class): int
    {
        $this->persistentObjectName = $class;

        $count = $this->cache->get($key, fn () => $this->countItems());

        return (int) $count;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    private function countItems(): int
    {
        /** @var PropertyRepository|CityRepository|DealTypeRepository|CategoryRepository|PageRepository|UserRepository $repository */
        $repository = $this->doctrine->getRepository($this->persistentObjectName);

        return $repository->countAll();
    }
}
