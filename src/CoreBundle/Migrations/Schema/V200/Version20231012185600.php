<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CourseBundle\Repository\CQuizRepository;
use App\Quiz\Domain\Entity\CQuiz;
use Doctrine\DBAL\Schema\Schema;
use Exception;

final class Version20231012185600 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate missing c_quiz items as resource nodes';
    }

    public function up(Schema $schema): void
    {
        $quizRepo = $this->container->get(CQuizRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);
        $userRepo = $this->container->get(UserRepository::class);

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);
            $courseRelUserList = $course->getTeachersSubscriptions();
            $courseAdmin = null;
            if (!empty($courseRelUserList)) {
                foreach ($courseRelUserList as $courseRelUser) {
                    $courseAdmin = $courseRelUser->getUser();

                    break;
                }
            }

            if ($courseAdmin === null) {
                $courseAdmin = $this->getAdmin();
            }

            // Quiz
            $sql = "SELECT * FROM c_quiz WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                try {
                    /** @var CQuiz $quiz */
                    $quiz = $quizRepo->find($id);
                    if ($quiz === null || $quiz->hasResourceNode()) {
                        continue;
                    }

                    error_log('Version20231012185600 checking quiz ' . $id . ' as resource node ');
                    $admin = $userRepo->find($courseAdmin->getId());
                    $quiz->setParent($course);
                    $resourceNode = $quizRepo->addResourceNode($quiz, $admin, $course);
                    $quiz->addCourseLink($course);
                    $this->entityManager->persist($resourceNode);
                    $this->entityManager->persist($quiz);
                    $this->entityManager->flush();
                } catch (Exception $e) {
                    error_log("Error processing quiz with ID {$id} in course {$courseId}: " . $e->getMessage());

                    continue;
                }
            }
        }
    }
}
