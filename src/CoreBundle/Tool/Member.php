<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

/**
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class Member extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'member';
    }

    public function getIcon(): string
    {
        return 'mdi-account';
    }

    public function getLink(): string
    {
        return '/main/user/user.php';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }
}
