<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CoreBundle\Entity\User\Usergroup as UsergroupEntity;

class UserGroup extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'usergroup';
    }

    public function getCategory(): string
    {
        return 'admin';
    }

    public function getIcon(): string
    {
        return 'mdi-account-group';
    }

    public function getLink(): string
    {
        return '/resources/usergroup/';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'usergroups' => UsergroupEntity::class,
        ];
    }
}
