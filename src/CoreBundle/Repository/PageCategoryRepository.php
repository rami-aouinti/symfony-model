<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\Platform\Domain\Entity\PageCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageCategory::class);
    }

    public function update(PageCategory $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    public function delete(?PageCategory $category = null): void
    {
        if ($category !== null) {
            $this->getEntityManager()->remove($category);
            $this->getEntityManager()->flush();
        }
    }
}
