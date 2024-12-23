<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Xapi\Domain\Entity\XApiLrsAuth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XApiLrsAuth>
 *
 * @method XApiLrsAuth|null find($id, $lockMode = null, $lockVersion = null)
 * @method XApiLrsAuth|null findOneBy(array $criteria, array $orderBy = null)
 * @method XApiLrsAuth[]    findAll()
 * @method XApiLrsAuth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XApiLrsAuthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XApiLrsAuth::class);
    }
}
