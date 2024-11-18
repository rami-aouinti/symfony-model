<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\Repository;

use App\Property\Domain\Entity\Property;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Throwable;

/**
 * @package App\Property\Infrastructure\Repository
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserPropertyRepository extends PropertyRepository
{
    public function findByUser(array $params): PaginationInterface
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.author = :id');

        if ($params['state'] === 'published') {
            $qb->andWhere("p.state = 'published'");
        } else {
            $qb->andWhere("p.state != 'published'");
        }

        $qb->orderBy('p.id', 'DESC')
            ->setParameter('id', $params['user']);

        return $this->createPaginator($qb->getQuery(), $params['page']);
    }

    public function changeState(Property $property, string $state): bool
    {
        try {
            $property->setState($state);
            $em = $this->getEntityManager();
            $em->persist($property);
            $em->flush();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
