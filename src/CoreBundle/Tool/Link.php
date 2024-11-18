<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CLink;
use App\CourseBundle\Entity\CLinkCategory;

/**
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class Link extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'link';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getIcon(): string
    {
        return 'mdi-file-link';
    }

    public function getLink(): string
    {
        return '/resources/links/:nodeId/';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'links' => CLink::class,
            'link_categories' => CLinkCategory::class,
        ];
    }
}
