<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository\Node;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class AccessUrlRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessUrl::class);
    }

    /**
     * Select the first access_url ID in the list as a default setting for
     * the creation of new users.
     */
    public function getFirstId(): int
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('MIN (a.id)');

        $q = $qb->getQuery();

        try {
            return (int)$q->getSingleScalarResult();
        } catch (NonUniqueResultException | NoResultException $e) {
            return 0;
        }
    }

    /**
     * @return array<int, AccessUrl>
     */
    public function findByUser(User $user): array
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('url');

        return $qb
            ->join('url.users', 'users')
            ->where($qb->expr()->eq('users.user', ':user'))
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}
