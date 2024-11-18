<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\Thematic\CThematic;

class CourseProgress extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'course_progress';
    }

    public function getIcon(): string
    {
        return 'mdi-progress-upload';
    }

    public function getLink(): string
    {
        return '/main/course_progress/index.php';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'thematics' => CThematic::class,
        ];
    }
}
