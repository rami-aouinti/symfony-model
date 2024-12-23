<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

class CourseHomePage extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'course_homepage';
    }

    public function getLink(): string
    {
        return '/resources/course_homepage';
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
        ];
    }
}
