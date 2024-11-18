<?php

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Platform\Domain\Entity\ColorTheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ColorThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ColorTheme::class);
    }
}
