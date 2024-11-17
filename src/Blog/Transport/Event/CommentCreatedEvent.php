<?php

declare(strict_types=1);

namespace App\Blog\Transport\Event;

use App\Blog\Domain\Entity\Comment;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package App\Event
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CommentCreatedEvent extends Event
{
    public function __construct(
        protected Comment $comment,
    ) {
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }
}
