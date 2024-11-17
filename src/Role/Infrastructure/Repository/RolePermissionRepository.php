<?php

declare(strict_types=1);

namespace App\Role\Infrastructure\Repository;

use App\Role\Domain\Entity\Role;
use App\Role\Domain\Entity\RolePermission;
use Doctrine\ORM\EntityRepository;

/**
 * @extends \Doctrine\ORM\EntityRepository<RolePermission>
 */
class RolePermissionRepository extends EntityRepository
{
    public function saveRolePermission(RolePermission $permission): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($permission);
        $entityManager->flush();
    }

    public function findRolePermission(Role $role, string $permission): ?RolePermission
    {
        return $this->findOneBy([
            'role' => $role,
            'permission' => $permission,
        ]);
    }

    /**
     * @return array<array<string, string|bool>>
     */
    public function getAllAsArray(): array
    {
        $qb = $this->createQueryBuilder('rp');

        $qb->select('r.name as role,rp.permission,rp.allowed')
            ->leftJoin('rp.role', 'r');

        return $qb->getQuery()->getArrayResult(); // @phpstan-ignore-line
    }
}
