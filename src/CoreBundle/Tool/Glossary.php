<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CGlossary;

class Glossary extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'glossary';
    }

    public function getLink(): string
    {
        return '/resources/glossary/:nodeId/';
    }

    public function getIcon(): string
    {
        return 'mdi-alphabetical';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'glossaries' => CGlossary::class,
        ];
    }
}
