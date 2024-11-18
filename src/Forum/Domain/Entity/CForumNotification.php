<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CForumNotification.
 */
#[ORM\Table(name: 'c_forum_notification')]
#[ORM\Index(columns: ['c_id'], name: 'course')]
#[ORM\Index(columns: ['thread_id'], name: 'thread')]
#[ORM\Index(columns: ['post_id'], name: 'post')]
#[ORM\Index(columns: ['user_id'], name: 'user_id')]
#[ORM\Index(columns: ['forum_id'], name: 'forum_id')]
#[ORM\Entity]
class CForumNotification
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\Column(name: 'c_id', type: 'integer')]
    protected int $cId;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    protected int $userId;

    #[ORM\Column(name: 'forum_id', type: 'integer')]
    protected int $forumId;

    #[ORM\Column(name: 'thread_id', type: 'integer')]
    protected int $threadId;

    #[ORM\Column(name: 'post_id', type: 'integer')]
    protected int $postId;

    public function __construct()
    {
        $this->forumId = 0;
        $this->threadId = 0;
        $this->postId = 0;
    }

    /**
     * Set cId.
     *
     * @return CForumNotification
     */
    public function setCId(int $cId)
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * Get cId.
     *
     * @return int
     */
    public function getCId()
    {
        return $this->cId;
    }

    /**
     * Set userId.
     *
     * @return CForumNotification
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set forumId.
     *
     * @return CForumNotification
     */
    public function setForumId(int $forumId)
    {
        $this->forumId = $forumId;

        return $this;
    }

    /**
     * Get forumId.
     *
     * @return int
     */
    public function getForumId()
    {
        return $this->forumId;
    }

    /**
     * Set threadId.
     *
     * @return CForumNotification
     */
    public function setThreadId(int $threadId)
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * Get threadId.
     *
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Set postId.
     *
     * @return CForumNotification
     */
    public function setPostId(int $postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * Get postId.
     *
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }
}
