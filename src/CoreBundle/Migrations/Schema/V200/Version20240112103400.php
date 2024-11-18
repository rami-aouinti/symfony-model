<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\SettingsCurrentRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20240112103400 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Change disk_quota of course to megabyte';
    }

    public function up(Schema $schema): void
    {
        $settingRepo = $this->container->get(SettingsCurrentRepository::class);

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $diskQuotaInBytes = $course->getDiskQuota();
            if ($diskQuotaInBytes !== null) {
                $diskQuotaInMegabytes = $diskQuotaInBytes / (1024 * 1024);
                $course->setDiskQuota((int)$diskQuotaInMegabytes);
                $this->entityManager->persist($course);
            }
        }
        $this->entityManager->flush();

        $setting = $settingRepo->findOneBy([
            'variable' => 'default_document_quotum',
        ]);
        if ($setting) {
            $selectedValueInBytes = (int)$setting->getSelectedValue() / (1024 * 1024);
            $setting->setSelectedValue((string)$selectedValueInBytes);
        }

        $setting = $settingRepo->findOneBy([
            'variable' => 'default_group_quotum',
        ]);
        if ($setting) {
            $selectedValueInBytes = (int)$setting->getSelectedValue() / (1024 * 1024);
            $setting->setSelectedValue((string)$selectedValueInBytes);
        }
        $this->entityManager->flush();
    }
}
