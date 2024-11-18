<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Blog\Domain\Entity\SocialPostAttachment;
use App\CoreBundle\Entity\Ticket\TicketMessageAttachment;
use App\CoreBundle\Entity\User\PersonalFile;
use App\Message\Domain\Entity\MessageAttachment;

/**
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class User extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'user';
    }

    public function getTitleToShow(): string
    {
        return 'Users';
    }

    public function getIcon(): string
    {
        return 'mdi-user';
    }

    public function getLink(): string
    {
        return '/';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'files' => PersonalFile::class,
            'message_attachments' => MessageAttachment::class,
            'ticket_message_attachments' => TicketMessageAttachment::class,
            'social_post_attachments' => SocialPostAttachment::class,
        ];
    }
}
