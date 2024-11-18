<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Repository\CDocumentRepository;
use App\CourseBundle\Repository\CQuizQuestionCategoryRepository;
use App\CourseBundle\Repository\CQuizQuestionRepository;
use App\CourseBundle\Repository\CQuizRepository;
use App\Quiz\Domain\Entity\CQuiz;
use App\Quiz\Domain\Entity\CQuizQuestion;
use App\Quiz\Domain\Entity\CQuizQuestionCategory;
use Doctrine\DBAL\Schema\Schema;

final class Version20201215142610 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_quiz, c_quiz_question_category, c_quiz_question';
    }

    public function up(Schema $schema): void
    {
        $quizRepo = $this->container->get(CQuizRepository::class);
        $quizQuestionRepo = $this->container->get(CQuizQuestionRepository::class);
        $quizQuestionCategoryRepo = $this->container->get(CQuizQuestionCategoryRepository::class);
        $documentRepo = $this->container->get(CDocumentRepository::class);
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

                /** @var CQuiz $resource */
                $resource = $quizRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $result = $this->fixItemProperty(
                    'quiz',
                    $quizRepo,
                    $course,
                    $courseAdmin,
                    $resource,
                    $course
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();

                /*$sql = "SELECT q.* FROM c_quiz_question q
                        INNER JOIN c_quiz_rel_question cq
                        ON (q.iid = cq.exercice_id and q.c_id = cq.c_id)
                        WHERE q.c_id = $courseId AND exercice_id = $id
                        ORDER BY iid";
                $result = $this->connection->executeQuery($sql);
                $questions = $result->fetchAllAssociative();
                foreach ($questions as $questionData) {
                    $questionData[''];
                }
                $sql = "SELECT * FROM c_quiz_question WHERE c_id = $courseId
                        ORDER BY iid";
                $result = $this->connection->executeQuery($sql);
                $items = $result->fetchAllAssociative();
                foreach ($items as $itemData) {
                }*/
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            // Question categories.
            $sql = "SELECT * FROM c_quiz_question_category WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();

            $course = $courseRepo->find($courseId);
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CQuizQuestionCategory $resource */
                $resource = $quizQuestionCategoryRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $result = $this->fixItemProperty(
                    'test_category',
                    $quizQuestionCategoryRepo,
                    $course,
                    $courseAdmin,
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

            $sql = "SELECT * FROM c_quiz_question WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];
                $course = $courseRepo->find($courseId);

                /** @var CQuizQuestion $question */
                $question = $quizQuestionRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $courseAdmin = $userRepo->find($courseAdmin->getId());
                $question->setParent($course);
                $resourceNode = $quizQuestionRepo->addResourceNode($question, $courseAdmin, $course);
                $question->addCourseLink($course);
                $this->entityManager->persist($resourceNode);
                $this->entityManager->persist($question);
                $this->entityManager->flush();

                /** @var CQuizQuestion $question */
                $question = $quizQuestionRepo->find($id);
                $pictureId = $question->getPicture();
                if (!empty($pictureId)) {
                    /** @var CDocument $document */
                    $document = $documentRepo->find($pictureId);
                    if ($document && $document->hasResourceNode() && $document->getResourceNode()->hasResourceFile()) {
                        $resourceFile = $document->getResourceNode()->getResourceFiles()->first();
                        $contents = $documentRepo->getResourceFileContent($document);
                        $quizQuestionRepo->addFileFromString($question, $resourceFile->getOriginalName(), $resourceFile->getMimeType(), $contents);
                    }
                }

                $this->entityManager->persist($question);
                $this->entityManager->flush();
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
