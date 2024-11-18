<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\Track\Domain\Entity\TrackEAttemptQualify;
use App\Track\Domain\Entity\TrackEExercise;
use Doctrine\DBAL\Schema\Schema;

class Version20230321164019 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate track_e_attempt_recording';
    }

    public function up(Schema $schema): void
    {
        $sql = 'SELECT * FROM track_e_attempt_recording';
        $result = $this->connection->executeQuery($sql);
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            $attemptQualify = new TrackEAttemptQualify();
            $attemptQualify
                ->setQuestionId($item['question_id'])
                ->setAnswer($item['answer'])
                ->setMarks($item['marks'])
                ->setAuthor($item['author'])
                ->setTeacherComment($item['teacher_comment'])
                ->setSessionId($item['session_id'])
            ;

            $trackEExercise = $this->entityManager->getRepository(TrackEExercise::class)->find($item['exe_id']);
            if ($trackEExercise) {
                $attemptQualify->setTrackExercise($trackEExercise);
                $this->entityManager->persist($attemptQualify);
            }
        }

        $this->entityManager->flush();
    }

    public function down(Schema $schema): void
    {
    }
}
