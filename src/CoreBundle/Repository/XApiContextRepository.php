<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Xapi\Domain\Entity\XApiContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XApiContext>
 *
 * @method XApiContext|null find($id, $lockMode = null, $lockVersion = null)
 * @method XApiContext|null findOneBy(array $criteria, array $orderBy = null)
 * @method XApiContext[]    findAll()
 * @method XApiContext[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XApiContextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XApiContext::class);
    }
}
