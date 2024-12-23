<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Repository\CDocumentRepository;
use App\Platform\Domain\Entity\Asset;
use App\Platform\Domain\Entity\AttemptFeedback;
use App\Platform\Domain\Entity\AttemptFile;
use App\Track\Domain\Entity\TrackEAttempt;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class Version20201212203625 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_document';
    }

    public function up(Schema $schema): void
    {
        $documentRepo = $this->container->get(CDocumentRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);
        $attemptRepo = $this->entityManager->getRepository(TrackEAttempt::class);

        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();

        $batchSize = self::BATCH_SIZE;

        // Migrate teacher exercise audio.
        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $sql = "SELECT iid, path
                    FROM c_document
                    WHERE
                          c_id = {$courseId} AND
                          path LIKE '/../exercises/teacher_audio%'
                    ";
            $result = $this->connection->executeQuery($sql);
            $documents = $result->fetchAllAssociative();

            foreach ($documents as $documentData) {
                $documentId = $documentData['iid'];
                $path = $documentData['path'];

                $path = str_replace('//', '/', $path);
                $path = str_replace('/../exercises/teacher_audio/', '', $path);

                $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/exercises/teacher_audio/' . $path;
                error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                if ($this->fileExists($filePath)) {
                    preg_match('#/(.*)/#', '/' . $path, $matches);
                    if (isset($matches[1]) && !empty($matches[1])) {
                        $attemptId = $matches[1];

                        /** @var TrackEAttempt $attempt */
                        $attempt = $attemptRepo->find($attemptId);
                        if ($attempt !== null) {
                            if ($attempt->getAttemptFeedbacks()->count() > 0) {
                                continue;
                            }

                            $fileName = basename($filePath);
                            $mimeType = mime_content_type($filePath);
                            $file = new UploadedFile($filePath, $fileName, $mimeType, null, true);
                            $asset = (new Asset())
                                ->setCategory(Asset::EXERCISE_FEEDBACK)
                                ->setTitle($fileName)
                                ->setFile($file)
                            ;
                            $this->entityManager->persist($asset);
                            $this->entityManager->flush();

                            $attempFeedback = (new AttemptFeedback())
                                ->setAsset($asset)
                            ;
                            $attempt->addAttemptFeedback($attempFeedback);
                            $this->entityManager->persist($attempFeedback);
                            $this->entityManager->flush();

                            /*$sql = "UPDATE c_document
                                    SET comment = 'skip_migrate'
                                    WHERE iid = $documentId
                            ";
                            $this->connection->executeQuery($sql);*/
                        }
                    }
                }
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Migrate student exercise audio
        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();

            $sql = "SELECT iid, path
                    FROM c_document
                    WHERE
                          c_id = {$courseId} AND
                          path NOT LIKE '/../exercises/teacher_audio%' AND
                          path LIKE '/../exercises/%'
                    ";
            $result = $this->connection->executeQuery($sql);
            $documents = $result->fetchAllAssociative();

            foreach ($documents as $documentData) {
                $documentId = $documentData['iid'];
                $path = $documentData['path'];

                $path = str_replace('//', '/', $path);
                $path = str_replace('/../exercises/', '', $path);

                $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/exercises/' . $path;
                error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                if ($this->fileExists($filePath)) {
                    $fileName = basename($filePath);
                    preg_match('#/(.*)/(.*)/(.*)/(.*)/#', '/' . $path, $matches);
                    $sessionId = $matches[1] ?? 0;
                    $exerciseId = $matches[2] ?? 0;
                    $questionId = $matches[3] ?? 0;
                    $userId = $matches[4] ?? 0;

                    /** @var TrackEAttempt $attempt */
                    $attempt = $attemptRepo->findOneBy([
                        'user' => $userId,
                        'questionId' => $questionId,
                        'filename' => $fileName,
                    ]);
                    if ($attempt !== null) {
                        if ($attempt->getAttemptFiles()->count() > 0) {
                            continue;
                        }

                        $mimeType = mime_content_type($filePath);
                        $file = new UploadedFile($filePath, $fileName, $mimeType, null, true);
                        $asset = (new Asset())
                            ->setCategory(Asset::EXERCISE_ATTEMPT)
                            ->setTitle($fileName)
                            ->setFile($file)
                        ;
                        $this->entityManager->persist($asset);
                        $this->entityManager->flush();

                        $attemptFile = (new AttemptFile())
                            ->setAsset($asset)
                        ;
                        $attempt->addAttemptFile($attemptFile);
                        $this->entityManager->persist($attemptFile);
                        $this->entityManager->flush();

                        /*$sql = "UPDATE c_document
                                SET comment = 'skip_migrate'
                                WHERE iid = $documentId
                        ";
                        $this->connection->executeQuery($sql);*/
                    }
                }
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Migrate normal documents.
        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $counter = 1;
            $courseId = $course->getId();

            $sql = "SELECT iid, path FROM c_document
                    WHERE
                          c_id = {$courseId} AND
                          path NOT LIKE '/../exercises/%' AND
                          path NOT LIKE '/chat_files/%'
                    ORDER BY filetype DESC, path";
            $result = $this->connection->executeQuery($sql);
            $documents = $result->fetchAllAssociative();
            foreach ($documents as $documentData) {
                $documentId = $documentData['iid'];
                $documentPath = $documentData['path'];
                $course = $courseRepo->find($courseId);

                /** @var CDocument $document */
                $document = $documentRepo->find($documentId);
                if ($document->hasResourceNode()) {
                    continue;
                }

                $parent = null;
                if (\dirname($documentPath) !== '.') {
                    $currentPath = \dirname($documentPath);
                    $sql = "SELECT iid FROM c_document
                    WHERE
                          c_id = {$courseId} AND
                          path LIKE '{$currentPath}'";
                    $result = $this->connection->executeQuery($sql);
                    $parentId = $result->fetchOne();

                    if (!empty($parentId)) {
                        $parent = $documentRepo->find($parentId);
                    }
                }

                if ($parent === null) {
                    $parent = $course;
                }
                if ($parent->getResourceNode() === null) {
                    continue;
                }
                $admin = $this->getAdmin();
                $result = $this->fixItemProperty('document', $documentRepo, $course, $admin, $document, $parent);

                if ($result === false) {
                    continue;
                }
                $documentPath = ltrim($documentPath, '/');
                $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/document/' . $documentPath;
                error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                $this->addLegacyFileToResource($filePath, $documentRepo, $document, $documentId);
                $this->entityManager->persist($document);

                if (($counter % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $counter++;
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function down(Schema $schema): void
    {
    }
}
