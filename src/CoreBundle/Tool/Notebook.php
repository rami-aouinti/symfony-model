<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CNotebook;

class Notebook extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'notebook';
    }

    public function getIcon(): string
    {
        return 'mdi-note';
    }

    public function getLink(): string
    {
        return '/main/notebook/index.php';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'notebooks' => CNotebook::class,
        ];
    }
}
