<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CoreBundle\Entity\Gradebook\GradebookCategory;
use App\CoreBundle\Entity\Gradebook\GradebookEvaluation;
use App\CoreBundle\Entity\Gradebook\GradebookLink;

class Gradebook extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'gradebook';
    }

    public function getTitleToShow(): string
    {
        return 'Assessments';
    }

    public function getLink(): string
    {
        return '/main/gradebook/index.php';
    }

    public function getIcon(): string
    {
        return 'mdi-certificate';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'gradebooks' => GradebookCategory::class,
            'gradebook_links' => GradebookLink::class,
            'gradebook_evaluations' => GradebookEvaluation::class,
        ];
    }
}
