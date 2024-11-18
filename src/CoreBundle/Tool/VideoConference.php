<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

/**
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class VideoConference extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'bbb';
    }

    public function getTitleToShow(): string
    {
        return 'Videoconference';
    }

    public function getIcon(): string
    {
        return 'mdi-video';
    }

    public function getLink(): string
    {
        return '/plugin/bbb/start.php';
    }

    public function getCategory(): string
    {
        return 'plugin';
    }

    public function getResourceTypes(): ?array
    {
        return [
        ];
    }
}
