<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Tool;

/**
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class Zoom extends AbstractPlugin
{
    public function getTitle(): string
    {
        return 'zoom';
    }

    public function getLink(): string
    {
        return 'plugin/zoom/start.php';
    }

    public function getIcon(): string
    {
        return 'mdi-video-box';
    }

    public function getTitleToShow(): string
    {
        return 'Zoom Videoconference';
    }
}
