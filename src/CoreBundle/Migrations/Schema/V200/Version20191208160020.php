<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use Doctrine\DBAL\Schema\Schema;

class Version20191208160020 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'ExtraFieldSavedSearch changes';
    }

    public function up(Schema $schema): void
    {
        // Delete empty values.
        $this->addSql(\sprintf("UPDATE extra_field_saved_search SET value = NULL WHERE value = '%s'", 's:0:"";'));

        $sql = "SELECT id, value FROM extra_field_saved_search WHERE value LIKE 's:%'";
        $result = $this->connection->executeQuery($sql);
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            $id = $item['id'];
            $value = 'a:1:{i:0;' . $item['value'] . '}';
            $sql = \sprintf("UPDATE extra_field_saved_search SET value = '%s' WHERE id = %s", $value, $id);
            $this->connection->executeQuery($sql);
        }
    }
}
