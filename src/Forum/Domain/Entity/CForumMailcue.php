<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Forum\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CForumMailcue
 *
 * @package App\Forum\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'c_forum_mailcue')]
#[ORM\Index(columns: ['c_id'], name: 'course')]
#[ORM\Index(columns: ['thread_id'], name: 'thread')]
#[ORM\Index(columns: ['user_id'], name: 'user')]
#[ORM\Index(columns: ['post_id'], name: 'post')]
#[ORM\Entity]
class CForumMailcue
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\Column(name: 'c_id', type: 'integer')]
    protected int $cId;

    #[ORM\Column(name: 'thread_id', type: 'integer')]
    protected int $threadId;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    protected int $userId;

    #[ORM\Column(name: 'post_id', type: 'integer')]
    protected int $postId;

    /**
     * Set cId.
     *
     * @return CForumMailcue
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
     * Set threadId.
     *
     * @return CForumMailcue
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
     * Set userId.
     *
     * @return CForumMailcue
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
     * Set postId.
     *
     * @return CForumMailcue
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
