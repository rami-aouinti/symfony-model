<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use Doctrine\DBAL\Schema\Schema;

class Version20231026221100 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migration of Add table social_post_attachments moved to Version20230720222140';
    }

    public function up(Schema $schema): void
    {
    }
}
