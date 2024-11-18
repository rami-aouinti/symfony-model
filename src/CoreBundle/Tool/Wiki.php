<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CWiki;

class Wiki extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'wiki';
    }

    public function getIcon(): string
    {
        return 'mdi-view-dashboard-edit';
    }

    public function getLink(): string
    {
        return '/main/wiki/index.php';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'wikis' => CWiki::class,
        ];
    }
}
