<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;

class GlobalTool extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'global';
    }

    public function getIcon(): string
    {
        return 'mdi-';
    }

    public function getLink(): string
    {
        return '/resources/chat';
    }

    public function getCategory(): string
    {
        return 'admin';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'urls' => AccessUrl::class,
            'courses' => Course::class,
            'users' => User::class,
        ];
    }
}
