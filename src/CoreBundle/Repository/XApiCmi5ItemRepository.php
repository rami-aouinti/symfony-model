<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\Xapi\Domain\Entity\XApiCmi5Item;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @extends NestedTreeRepository<XApiCmi5Item>
 *
 * @method XApiCmi5Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method XApiCmi5Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method XApiCmi5Item[]    findAll()
 * @method XApiCmi5Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XApiCmi5ItemRepository extends NestedTreeRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(XApiCmi5Item::class));
    }
}
