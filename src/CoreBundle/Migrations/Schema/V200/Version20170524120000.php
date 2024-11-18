<?php

declare(strict_types=1);

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use Doctrine\DBAL\Schema\Schema;

final class Version20170524120000 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Replace "name" with "title" fields in tables (part 3)';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('contact_form_contact_category')) {
            $this->addSql(
                'ALTER TABLE contact_form_contact_category CHANGE name title VARCHAR(255) NOT NULL'
            );
        }

        if ($schema->hasTable('fos_group')) {
            $table = $schema->getTable('tool');
            if ($table->hasIndex('UNIQ_4B019DDB5E237E06')) {
                $this->addSql(
                    'DROP INDEX UNIQ_4B019DDB5E237E06 on fos_group'
                );
            }
            $this->addSql(
                'CREATE UNIQUE INDEX UNIQ_4B019DDB2B36786B ON fos_group (title)'
            );
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('fos_group');
        if ($table->hasIndex('UNIQ_4B019DDB2B36786B')) {
            $this->addSql(
                'DROP INDEX UNIQ_4B019DDB2B36786B on fos_group'
            );
        }

        $table = $schema->getTable('contact_form_contact_category');
        if ($table->hasColumn('title')) {
            $this->addSql('ALTER TABLE contact_form_contact_category CHANGE title name VARCHAR(255) NOT NULL');
        }
    }
}
