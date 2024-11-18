<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\Announcement\Domain\Entity\CAnnouncementAttachment;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Repository\CAnnouncementAttachmentRepository;
use App\CourseBundle\Repository\CAnnouncementRepository;
use App\Quiz\Domain\Entity\CQuiz;
use Doctrine\DBAL\Schema\Schema;

final class Version20201215153517 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_announcement, c_announcement_attachment';
    }

    public function up(Schema $schema): void
    {
        $announcementRepo = $this->container->get(CAnnouncementRepository::class);
        $announcementAttachmentRepo = $this->container->get(CAnnouncementAttachmentRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();
        $admin = $this->getAdmin();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            $sql = "SELECT * FROM c_announcement WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CQuiz $resource */
                $resource = $announcementRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $result = $this->fixItemProperty(
                    'announcement',
                    $announcementRepo,
                    $course,
                    $admin,
                    $resource,
                    $course
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }

            $sql = "SELECT * FROM c_announcement_attachment WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];
                $path = $itemData['path'];
                $fileName = $itemData['filename'];

                /** @var CAnnouncementAttachment $resource */
                $resource = $announcementAttachmentRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $result = $this->fixItemProperty(
                    'announcement_attachment',
                    $announcementAttachmentRepo,
                    $course,
                    $admin,
                    $resource,
                    $course
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();

                $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/upload/announcements/' . $path;
                error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                $this->addLegacyFileToResource($filePath, $announcementAttachmentRepo, $resource, $id, $fileName);
                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }
        }
    }
}
