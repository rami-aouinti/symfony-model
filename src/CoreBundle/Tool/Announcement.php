<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Announcement\Domain\Entity\CAnnouncement;
use App\Announcement\Domain\Entity\CAnnouncementAttachment;

class Announcement extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'announcement';
    }

    public function getIcon(): string
    {
        return 'mdi-bullhorn';
    }

    public function getLink(): string
    {
        return '/main/announcements/announcements.php';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'announcements' => CAnnouncement::class,
            'announcements_attachments' => CAnnouncementAttachment::class,
        ];
    }
}
