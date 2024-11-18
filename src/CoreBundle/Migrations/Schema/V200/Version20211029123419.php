<?php

declare(strict_types=1);

namespace App\CoreBundle\Migrations\Schema\V200;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Component\Utils\CreateDefaultPages;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\AccessUrlRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20211029123419 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Page entity';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('page')) {
            $createDefaultPages = $this->container->get(CreateDefaultPages::class);

            $urlRepo = $this->container->get(AccessUrlRepository::class);
            $urlList = $urlRepo->findAll();

            /** @var AccessUrl $url */
            $url = $urlList[0];
            $createDefaultPages->createDefaultPages($this->getAdmin(), $url, 'en_US');
        }
    }

    public function down(Schema $schema): void
    {
    }
}
