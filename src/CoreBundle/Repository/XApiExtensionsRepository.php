<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Xapi\Domain\Entity\XApiExtensions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XApiExtensions>
 *
 * @method XApiExtensions|null find($id, $lockMode = null, $lockVersion = null)
 * @method XApiExtensions|null findOneBy(array $criteria, array $orderBy = null)
 * @method XApiExtensions[]    findAll()
 * @method XApiExtensions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XApiExtensionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XApiExtensions::class);
    }
}
