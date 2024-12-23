<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Platform\Domain\Entity\Illustration;

class Asset extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'asset';
    }

    public function getCategory(): string
    {
        return 'admin';
    }

    public function getLink(): string
    {
        return '/';
    }

    public function getIcon(): string
    {
        return 'admin';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'illustrations' => Illustration::class,
        ];
    }
}
