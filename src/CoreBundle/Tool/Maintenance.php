<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

class Maintenance extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'course_maintenance';
    }

    public function getIcon(): string
    {
        return 'mdi-wrench-cog';
    }

    public function getLink(): string
    {
        return '/main/course_info/maintenance.php';
    }

    public function getCategory(): string
    {
        return 'admin';
    }
}
