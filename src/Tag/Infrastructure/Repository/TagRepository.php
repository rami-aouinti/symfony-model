<?php

declare(strict_types=1);

namespace App\Tag\Infrastructure\Repository;

use App\Tag\Domain\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package App\Repository
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }
}
