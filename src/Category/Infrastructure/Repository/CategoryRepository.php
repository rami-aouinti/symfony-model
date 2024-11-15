<?php

declare(strict_types=1);

namespace App\Category\Infrastructure\Repository;

use App\Category\Domain\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package App\Repository
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }
}
