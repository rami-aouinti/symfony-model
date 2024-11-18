<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Entity\CGlossary;
use App\CourseBundle\Repository\CGlossaryRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20201216120654 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_glossary';
    }

    public function up(Schema $schema): void
    {
        $glossaryRepo = $this->container->get(CGlossaryRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        $admin = $this->getAdmin();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            // Glossary.
            $sql = "SELECT * FROM c_glossary WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CGlossary $resource */
                $resource = $glossaryRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $result = $this->fixItemProperty(
                    'glossary',
                    $glossaryRepo,
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
        }
    }
}
