<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CLp\CLp;
use App\CourseBundle\Entity\CLp\CLpCategory;

class LearningPath extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'learnpath';
    }

    public function getTitleToShow(): string
    {
        return 'Learning paths';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getLink(): string
    {
        return '/main/lp/lp_controller.php';
    }

    public function getIcon(): string
    {
        return 'mdi-map-marker-path';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'lps' => CLp::class,
            'lp_categories' => CLpCategory::class,
        ];
    }
}
