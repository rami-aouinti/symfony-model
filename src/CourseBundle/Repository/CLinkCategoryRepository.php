<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\CLinkCategory;
use Doctrine\Persistence\ManagerRegistry;

final class CLinkCategoryRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CLinkCategory::class);
    }

    /*public function getResources(User $user, ResourceNode $parentNode, Course $course = null, Session $session = null, CGroup $group = null): QueryBuilder
    {
        return $this->getResourcesByCourse($course, $session, $group, $parentNode);
    }*/
}
