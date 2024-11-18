<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\Survey\CSurvey;
use App\CourseBundle\Entity\Survey\CSurveyQuestion;

class Survey extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'survey';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getIcon(): string
    {
        return 'mdi-form-dropdown';
    }

    public function getLink(): string
    {
        return '/main/survey/survey_list.php';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'surveys' => CSurvey::class,
            'survey_questions' => CSurveyQuestion::class,
        ];
    }
}
