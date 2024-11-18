<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Traits\NonResourceRepository;
use App\CoreBundle\Traits\Repository\ORM\NestedTreeRepositoryTrait;
use App\CourseBundle\Entity\CLp\CLpItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CLpItemRepository extends ServiceEntityRepository
{
    use NestedTreeRepositoryTrait;
    use NonResourceRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CLpItem::class);

        $this->initializeTreeRepository($this->getEntityManager(), $this->getClassMetadata());
    }

    public function create(CLpItem $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    public function getRootItem(int $lpId): ?CLpItem
    {
        return $this->findOneBy([
            'path' => 'root',
            'lp' => $lpId,
        ]);
    }

    public function findItemsByLearningPathAndType(int $learningPathId, string $itemType): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.lp = :learningPathId')
            ->andWhere('i.itemType = :itemType')
            ->setParameter('learningPathId', $learningPathId)
            ->setParameter('itemType', $itemType)
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
