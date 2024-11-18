<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\Attendance\CAttendance;

class Attendance extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'attendance';
    }

    public function getLink(): string
    {
        return '/main/attendance/index.php';
    }

    public function getIcon(): string
    {
        return 'mdi-av-timer';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'attendances' => CAttendance::class,
        ];
    }
}
