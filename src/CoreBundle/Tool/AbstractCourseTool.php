<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

abstract class AbstractCourseTool extends AbstractTool
{
    public function isCourseTool(): bool
    {
        return true;
    }
}
