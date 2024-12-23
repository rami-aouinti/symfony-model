<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\SessionRepository;
use App\CourseBundle\Entity\Group\CGroup;
use App\CourseBundle\Entity\Group\CGroupCategory;
use App\CourseBundle\Repository\CGroupCategoryRepository;
use App\CourseBundle\Repository\CGroupRepository;
use App\Platform\Domain\Entity\ResourceLink;
use Doctrine\DBAL\Schema\Schema;

final class Version20201212195112 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_group_info ';
    }

    public function up(Schema $schema): void
    {
        $courseRepo = $this->container->get(CourseRepository::class);
        $sessionRepo = $this->container->get(SessionRepository::class);
        $groupRepo = $this->container->get(CGroupRepository::class);
        $groupCategoryRepo = $this->container->get(CGroupCategoryRepository::class);

        $batchSize = self::BATCH_SIZE;

        // Migrating c_tool.
        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            // Categories
            $counter = 1;
            $courseId = $course->getId();
            $sql = "SELECT * FROM c_group_category
                    WHERE c_id = {$courseId} ";
            $result = $this->connection->executeQuery($sql);
            $categories = $result->fetchAllAssociative();

            foreach ($categories as $categoryData) {
                /** @var CGroupCategory $category */
                $category = $groupCategoryRepo->find($categoryData['iid']);
                if ($category->hasResourceNode()) {
                    continue;
                }

                $course = $courseRepo->find($courseId);
                $session = null;
                /*if (!empty($groupData['session_id'])) {
                    $session = $sessionRepo->find($groupData['session_id']);
                }*/

                $admin = $this->getAdmin();
                $category->setParent($course);
                $groupRepo->addResourceNode($category, $admin, $course);
                $newVisibility = ResourceLink::VISIBILITY_PUBLISHED;
                $category->addCourseLink($course, $session, null, $newVisibility);
                $this->entityManager->persist($category);
                if (($counter % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $counter++;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            // Groups
            $counter = 1;
            $courseId = $course->getId();
            $sql = "SELECT * FROM c_group_info
                    WHERE c_id = {$courseId} ";
            $result = $this->connection->executeQuery($sql);
            $groups = $result->fetchAllAssociative();

            foreach ($groups as $groupData) {
                /** @var CGroup $group */
                $group = $groupRepo->find($groupData['iid']);
                if ($group->hasResourceNode()) {
                    continue;
                }

                $course = $courseRepo->find($courseId);
                $session = null;
                if (!empty($groupData['session_id'])) {
                    $session = $sessionRepo->find($groupData['session_id']);
                }

                $admin = $this->getAdmin();
                $group->setParent($course);
                $groupRepo->addResourceNode($group, $admin, $course);
                $newVisibility = ResourceLink::VISIBILITY_PENDING;
                if ($group->getStatus() === 1) {
                    $newVisibility = ResourceLink::VISIBILITY_PUBLISHED;
                }
                $group->addCourseLink($course, $session, null, $newVisibility);
                $this->entityManager->persist($group);
                if (($counter % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $counter++;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
