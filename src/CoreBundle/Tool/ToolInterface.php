<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

/**
 *
 */
interface ToolInterface
{
    public function getTitle(): string;

    public function getCategory(): string;

    public function getLink(): string;

    public function getIcon(): string;
}
