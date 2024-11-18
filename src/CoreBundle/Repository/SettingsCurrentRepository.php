<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\Platform\Domain\Entity\SettingsCurrent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SettingsCurrentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SettingsCurrent::class);
    }

    public function update(SettingsCurrent $setting): void
    {
        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();
    }
}
