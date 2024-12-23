<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use Doctrine\DBAL\Schema\Schema;

final class Version20240713125400 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Replace ROLE_RRHH with ROLE_HR in user.roles';
    }

    public function up(Schema $schema): void
    {
        $conn = $this->connection;

        $users = $conn->fetchAllAssociative("SELECT id, roles FROM user WHERE roles LIKE '%ROLE_RRHH%'");

        foreach ($users as $user) {
            $roles = unserialize($user['roles']);

            if ($roles !== false) {
                $updatedRoles = array_map(function ($role) {
                    return $role === 'ROLE_RRHH' ? 'ROLE_HR' : $role;
                }, $roles);

                $newRolesSerialized = serialize($updatedRoles);
                $conn->executeUpdate(
                    'UPDATE user SET roles = ? WHERE id = ?',
                    [$newRolesSerialized, $user['id']]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $conn = $this->connection;

        $users = $conn->fetchAllAssociative("SELECT id, roles FROM user WHERE roles LIKE '%ROLE_HR%'");

        foreach ($users as $user) {
            $roles = unserialize($user['roles']);

            if ($roles !== false) {
                $updatedRoles = array_map(function ($role) {
                    return $role === 'ROLE_HR' ? 'ROLE_RRHH' : $role;
                }, $roles);

                $newRolesSerialized = serialize($updatedRoles);

                $conn->executeUpdate(
                    'UPDATE user SET roles = ? WHERE id = ?',
                    [$newRolesSerialized, $user['id']]
                );
            }
        }
    }
}
