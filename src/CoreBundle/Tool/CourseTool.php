<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CTool;
use App\CourseBundle\Entity\CToolIntro;

class CourseTool extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'course_tool';
    }

    public function getLink(): string
    {
        return '/resources/course_tool/links';
    }

    public function getIcon(): string
    {
        return 'mdi-file-link';
    }

    public function getCategory(): string
    {
        return 'admin';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'links' => CTool::class,
            'introductions' => CToolIntro::class,
        ];
    }
}
