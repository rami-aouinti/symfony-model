<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Quiz\Domain\Entity\CQuiz;
use App\Quiz\Domain\Entity\CQuizCategory;
use App\Quiz\Domain\Entity\CQuizQuestion;
use App\Quiz\Domain\Entity\CQuizQuestionCategory;

class Exercise extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'quiz';
    }

    public function getTitleToShow(): string
    {
        return 'Tests';
    }

    public function getIcon(): string
    {
        return 'mdi-order-bool-ascending-variant';
    }

    public function getLink(): string
    {
        return '/main/exercise/exercise.php';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'exercises' => CQuiz::class,
            'questions' => CQuizQuestion::class,
            'question_categories' => CQuizQuestionCategory::class,
            'exercise_categories' => CQuizCategory::class,
        ];
    }
}
