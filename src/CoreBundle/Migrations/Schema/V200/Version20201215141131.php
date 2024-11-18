<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Entity\CLink;
use App\CourseBundle\Entity\CLinkCategory;
use App\CourseBundle\Repository\CLinkCategoryRepository;
use App\CourseBundle\Repository\CLinkRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20201215141131 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_link_category, c_link';
    }

    public function up(Schema $schema): void
    {
        $linkRepo = $this->container->get(CLinkRepository::class);
        $linkCategoryRepo = $this->container->get(CLinkCategoryRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        $admin = $this->getAdmin();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $counter = 1;
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            $sql = "SELECT * FROM c_link_category WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CLinkCategory $event */
                $resource = $linkCategoryRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $result = $this->fixItemProperty(
                    'link_category',
                    $linkCategoryRepo,
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

            $sql = "SELECT * FROM c_link WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];
                $categoryId = $itemData['category_id'];

                /** @var CLink $event */
                $resource = $linkRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $parent = $course;

                if (!empty($categoryId)) {
                    $category = $linkCategoryRepo->find($categoryId);
                    if ($category !== null) {
                        $parent = $category;
                    }
                }

                $result = $this->fixItemProperty(
                    'link',
                    $linkRepo,
                    $course,
                    $admin,
                    $resource,
                    $parent
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
