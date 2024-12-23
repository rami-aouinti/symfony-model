<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CourseBundle\Entity\CLp\CLp;
use App\CourseBundle\Repository\CLpRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20230615213500 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_lp to resource node position';
    }

    public function up(Schema $schema): void
    {
        $lpRepo = $this->container->get(CLpRepository::class);

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();

            $sql = "SELECT * FROM c_lp WHERE c_id = {$courseId} ORDER BY display_order";
            $result = $this->connection->executeQuery($sql);
            $lps = $result->fetchAllAssociative();

            foreach ($lps as $lp) {
                $lpId = (int)$lp['iid'];
                $position = (int)$lp['display_order'];

                /** @var CLp $resource */
                $resource = $lpRepo->find($lpId);
                if ($resource->hasResourceNode()) {
                    $resourceNode = $resource->getResourceNode();

                    $course = $this->findCourse((int)$lp['c_id']);
                    $session = $this->findSession((int)($lp['session_id'] ?? 0));

                    $link = $resourceNode->getResourceLinkByContext($course, $session);

                    $link?->setDisplayOrder(
                        $position > 0 ? $position - 1 : 0
                    );
                }
            }
        }

        $this->entityManager->flush();
    }
}
