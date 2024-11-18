<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Entity\CWiki;
use App\CourseBundle\Repository\CWikiRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20201219115244 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_wiki';
    }

    public function up(Schema $schema): void
    {
        $wikiRepo = $this->container->get(CWikiRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        $admin = $this->getAdmin();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            $sql = "SELECT * FROM c_wiki WHERE c_id = {$courseId} ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CWiki $resource */
                $resource = $wikiRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $result = $this->fixItemProperty(
                    'wiki',
                    $wikiRepo,
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

            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
