<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CoreBundle\Repository\SessionRepository;
use App\CourseBundle\Entity\CLp\CLpRelUser;
use App\CourseBundle\Repository\CLpRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20230215072918 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate learnpath subscription';
    }

    public function up(Schema $schema): void
    {
        $lpRepo = $this->container->get(CLpRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);
        $sessionRepo = $this->container->get(SessionRepository::class);
        $userRepo = $this->container->get(UserRepository::class);

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            $sql = "SELECT * FROM c_lp WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $lps = $result->fetchAllAssociative();
            foreach ($lps as $lpData) {
                $id = $lpData['iid'];
                $lp = $lpRepo->find($id);
                $sql = "SELECT * FROM c_item_property
                        WHERE tool = 'learnpath' AND c_id = {$courseId} AND ref = {$id} AND lastedit_type = 'LearnpathSubscription'";
                $result = $this->connection->executeQuery($sql);
                $items = $result->fetchAllAssociative();

                if (!empty($items)) {
                    foreach ($items as $item) {
                        if (!($item['to_user_id'] === null || $item['to_user_id'] === 0)) {
                            $sessionId = $item['session_id'] ?? 0;
                            $userId = $item['to_user_id'] ?? 0;
                            $session = $sessionRepo->find($sessionId);
                            $user = $userRepo->find($userId);
                            $item = new CLpRelUser();
                            $item
                                ->setUser($user)
                                ->setCourse($course)
                                ->setLp($lp)
                            ;
                            if (!empty($session)) {
                                $item->setSession($session);
                            }
                            $this->entityManager->persist($item);
                            $this->entityManager->flush();
                        }
                    }
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
    }
}
