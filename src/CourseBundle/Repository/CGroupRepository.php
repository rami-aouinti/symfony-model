<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\Group\CGroup;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class CGroupRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CGroup::class);
    }

    public function findAllByCourse(
        Course $course,
        ?Session $session = null,
        ?string $title = null,
        ?int $status = null,
        ?int $categoryId = null
    ): QueryBuilder {
        $qb = $this->getResourcesByCourse($course, $session);

        $this->addStatusQueryBuilder($status, $qb);
        $this->addCategoryQueryBuilder($categoryId, $qb);
        $this->addTitleQueryBuilder($title, $qb);

        return $qb;
    }

    public function findOneByTitle(string $name): ?CGroup
    {
        return $this->findOneBy(
            [
                'title' => $name,
            ]
        );
    }

    private function addStatusQueryBuilder(?int $status = null, ?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);

        if ($status !== null) {
            $qb
                ->andWhere('resource.status = :status')
                ->setParameter('status', $status)
            ;
        }

        return $qb;
    }

    private function addCategoryQueryBuilder(?int $categoryId = null, ?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);

        if ($categoryId === null) {
            $qb
                ->andWhere('resource.category is NULL')
            ;
        } else {
            $qb
                ->andWhere('resource.category = :category')
                ->setParameter('category', $categoryId)
            ;
        }

        return $qb;
    }
}
