<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

use App\Track\Domain\Entity\TrackEAttempt;
use App\Track\Domain\Entity\TrackEExercises;
use App\Quiz\Domain\Entity\CQuiz;
use App\Quiz\Domain\Entity\CQuizQuestion;
use Chamilo\PluginBundle\XApi\ToolExperience\Statement\QuizQuestionAnswered;

class XApiQuizQuestionAnsweredHookObserver extends XApiActivityHookObserver implements HookQuizQuestionAnsweredObserverInterface
{
    public function hookQuizQuestionAnswered(HookQuizQuestionAnsweredEventInterface $event): void
    {
        $data = $event->getEventData();

        $em = Database::getManager();
        $attemptRepo = $em->getRepository(TrackEAttempt::class);

        $exe = $em->find(TrackEExercises::class, $data['exe_id']);
        $question = $em->find(CQuizQuestion::class, $data['question']['id']);
        $attempt = $attemptRepo->findOneBy(
            [
                'exeId' => $exe->getExeId(),
                'questionId' => $question->getId(),
            ]
        );
        $quiz = $em->find(CQuiz::class, $data['quiz']['id']);

        $quizQuestionAnswered = new QuizQuestionAnswered($attempt, $question, $quiz);

        $statement = $quizQuestionAnswered->generate();

        $this->saveSharedStatement($statement);
    }
}
