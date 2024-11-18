<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Xapi\Domain\Entity\XApiActor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XApiActor>
 *
 * @method XApiActor|null find($id, $lockMode = null, $lockVersion = null)
 * @method XApiActor|null findOneBy(array $criteria, array $orderBy = null)
 * @method XApiActor[]    findAll()
 * @method XApiActor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XApiActorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XApiActor::class);
    }
}
