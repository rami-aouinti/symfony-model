<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\Platform\Domain\Entity\Templates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class TemplatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Templates::class);
    }

    /**
     * Get the course template for a user.
     *
     * @return array
     */
    public function getCourseTemplates(Course $course, User $user)
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('t', 'd.path')
            ->innerJoin(
                'ChamiloCoreBundle:Course',
                'c',
                Join::WITH,
                't.id = c.id'
            )
            ->innerJoin(
                'ChamiloCourseBundle:CDocument',
                'd',
                Join::WITH,
                'c.id = d.course'
            )
            ->where(
                $qb->expr()->eq('d.iid', 't.refDoc')
            )
            ->andWhere(
                $qb->expr()->eq('c.id', $course->getId())
            )
            ->andWhere(
                $qb->expr()->eq('t.userId', $user->getId())
            )
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCourseDocumentTemplates(Course $course)
    {
        return $this->createQueryBuilder('t')
            ->where('t.course = :course')
            ->andWhere('t.refDoc IS NOT NULL')
            ->andWhere('t.refDoc > 0')
            ->setParameter('course', $course)
            ->getQuery()
            ->getResult()
        ;
    }
}
