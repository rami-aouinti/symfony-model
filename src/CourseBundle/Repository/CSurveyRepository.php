<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use App\CoreBundle\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use App\CourseBundle\Entity\Survey\CSurvey;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class CSurveyRepository extends ResourceRepository
{
    use NestedTreeRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CSurvey::class);

        $this->initializeTreeRepository($this->getEntityManager(), $this->getClassMetadata());
    }

    public function findAllByCourse(
        Course $course,
        ?Session $session = null,
        ?string $title = null,
        ?string $language = null,
        ?User $author = null
    ): QueryBuilder {
        $qb = $this->getResourcesByCourse($course, $session);

        $this->addTitleQueryBuilder($title, $qb);
        $this->addLanguageQueryBuilder($language, $qb);
        $this->addCreatorQueryBuilder($author, $qb);

        return $qb;
    }

    protected function addTitleQueryBuilder(?string $title, ?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);
        if ($title === null) {
            return $qb;
        }

        $qb
            ->andWhere('resource.code = :title')
            ->andWhere('node.title = :title')
            ->setParameter('title', $title)
        ;

        return $qb;
    }

    private function addLanguageQueryBuilder(?string $language = null, ?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);

        if ($language !== null) {
            $qb
                ->andWhere('resource.lang = :lang')
                ->setParameter('lang', $language)
            ;
        }

        return $qb;
    }
}
