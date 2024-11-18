<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Traits\NonResourceRepository;
use App\CourseBundle\Entity\Thematic\CThematicAdvance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CThematicAdvanceRepository extends ServiceEntityRepository
{
    use NonResourceRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CThematicAdvance::class);
    }

    public function delete(CThematicAdvance $resource): void
    {
        $this->getEntityManager()->remove($resource);
        $this->getEntityManager()->flush();
    }
}
