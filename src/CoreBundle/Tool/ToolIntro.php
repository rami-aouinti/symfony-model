<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CToolIntro;

class ToolIntro extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'tool_intro';
    }

    public function getIcon(): string
    {
        return 'mdi-certificate';
    }

    public function getLink(): string
    {
        return '/resources/ctoolintro';
    }

    public function getCategory(): string
    {
        return 'tool';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'tool_intro' => CToolIntro::class,
        ];
    }
}
