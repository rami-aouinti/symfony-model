<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\CChatConversation;

class Chat extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'chat';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getIcon(): string
    {
        return 'mdi-chat-processing';
    }

    public function getLink(): string
    {
        return '/resources/chat';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'conversations' => CChatConversation::class,
        ];
    }
}
