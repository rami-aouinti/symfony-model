<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\Group\CGroup;
use App\CourseBundle\Entity\Group\CGroupCategory;

class Group extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'group';
    }

    public function getTitleToShow(): string
    {
        return 'Groups';
    }

    public function getLink(): string
    {
        return '/main/group/group.php';
    }

    public function getIcon(): string
    {
        return 'mdi-account-group';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'groups' => CGroup::class,
            'group_categories' => CGroupCategory::class,
        ];
    }
}
