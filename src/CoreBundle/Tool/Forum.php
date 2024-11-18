<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\Forum\Domain\Entity\CForum;
use App\Forum\Domain\Entity\CForumAttachment;
use App\Forum\Domain\Entity\CForumCategory;
use App\Forum\Domain\Entity\CForumPost;
use App\Forum\Domain\Entity\CForumThread;

class Forum extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'forum';
    }

    public function getCategory(): string
    {
        return 'authoring';
    }

    public function getLink(): string
    {
        return '/main/forum/index.php';
    }

    public function getIcon(): string
    {
        return 'mdi-comment-quote';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'forums' => CForum::class,
            'forum_attachments' => CForumAttachment::class,
            'forum_categories' => CForumCategory::class,
            'forum_posts' => CForumPost::class,
            'forum_threads' => CForumThread::class,
        ];
    }
}
