<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\User;

use App\CoreBundle\Traits\UserTrait;
use App\Platform\Domain\Entity\Tag;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserRelTag.
 */
#[ORM\Table(name: 'user_rel_tag')]
#[ORM\Index(name: 'idx_urt_uid', columns: ['user_id'])]
#[ORM\Index(name: 'idx_urt_tid', columns: ['tag_id'])]
#[ORM\Entity]
class UserRelTag
{
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'userRelTags')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: Tag::class, cascade: ['persist'], inversedBy: 'userRelTags')]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Tag $tag = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
