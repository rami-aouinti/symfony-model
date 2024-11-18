<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Tool;

abstract class AbstractPlugin extends AbstractCourseTool implements ToolInterface
{
    public function getCategory(): string
    {
        return 'plugin';
    }
}
