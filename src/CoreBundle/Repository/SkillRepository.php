<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\Skill\Skill;
use App\CoreBundle\Entity\User\User;
use App\Session\Domain\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Angel Fernando Quiroz Campos <angel.quiroz@beeznest.com>
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    public function deleteAsset(Skill $skill): void
    {
        if ($skill->hasAsset()) {
            $asset = $skill->getAsset();
            $skill->setAsset(null);

            $this->getEntityManager()->persist($skill);
            $this->getEntityManager()->remove($asset);
            $this->getEntityManager()->flush();
        }
    }

    public function update(Skill $skill): void
    {
        $this->getEntityManager()->persist($skill);
        $this->getEntityManager()->flush();
    }

    public function delete(Skill $skill): void
    {
        $this->getEntityManager()->remove($skill);
        $this->getEntityManager()->flush();
    }

    /**
     * Get the last acquired skill by a user on course and/or session.
     */
    public function getLastByUser(User $user, ?Course $course = null, ?Session $session = null): ?Skill
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->innerJoin(
                'ChamiloCoreBundle:SkillRelUser',
                'su',
                Join::WITH,
                's.id = su.skill'
            )
            ->where(
                $qb->expr()->eq('su.user', $user->getId())
            )
        ;

        if ($course !== null) {
            $qb->andWhere(
                $qb->expr()->eq('su.course', $course->getId())
            );
        }

        if ($session !== null) {
            $qb->andWhere(
                $qb->expr()->eq('su.session', $session->getId())
            );
        }

        $qb
            ->setMaxResults(1)
            ->orderBy('su.id', Criteria::DESC)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
