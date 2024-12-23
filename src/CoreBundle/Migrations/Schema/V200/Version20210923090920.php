<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\Kernel;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class Version20210923090920 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Course pictures';
    }

    public function up(Schema $schema): void
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();
        $illustrationRepo = $this->container->get(IllustrationRepository::class);
        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $directory = $course->getDirectory();
            if (empty($directory)) {
                continue;
            }

            if ($illustrationRepo->hasIllustration($course)) {
                continue;
            }

            $picturePath = $this->getUpdateRootPath() . '/app/courses/' . $directory . '/course-pic.png';
            error_log('MIGRATIONS :: $filePath -- ' . $picturePath . ' ...');
            if ($this->fileExists($picturePath)) {
                $admin = $this->getAdmin();
                $mimeType = mime_content_type($picturePath);
                $uploadFile = new UploadedFile($picturePath, 'course-pic', $mimeType, null, true);
                $illustrationRepo->addIllustration(
                    $course,
                    $admin,
                    $uploadFile
                );
                $this->entityManager->persist($course);
                $this->entityManager->flush();
            }
        }
    }
}
