<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\Group\CGroupCategory;
use Doctrine\Persistence\ManagerRegistry;

final class CGroupCategoryRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CGroupCategory::class);
    }
}
