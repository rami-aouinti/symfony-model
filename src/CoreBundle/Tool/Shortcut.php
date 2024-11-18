<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CShortcut;
use App\LtiBundle\Entity\ExternalTool;

class Shortcut extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'shortcuts';
    }

    public function getIcon(): string
    {
        return 'mdi';
    }

    public function getLink(): string
    {
        return '/';
    }

    public function getCategory(): string
    {
        return 'admin';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'shortcuts' => CShortcut::class,
            'external_tools' => ExternalTool::class,
        ];
    }
}
