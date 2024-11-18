<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\CCourseDescription;
use App\CourseBundle\Entity\Group\CGroup;
use App\Session\Domain\Entity\Session;
use Doctrine\Persistence\ManagerRegistry;

final class CCourseDescriptionRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCourseDescription::class);
    }

    public function findByTypeInCourse(int $type, Course $course, ?Session $session = null, ?CGroup $group = null): array
    {
        $qb = $this->getResourcesByCourse($course, $session, $group)
            ->andWhere('resource.descriptionType = :description_type')
            ->setParameter('description_type', $type)
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
