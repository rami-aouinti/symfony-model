<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Entity\Group;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * CGroupRelUser.
 */
#[ORM\Table(name: 'c_group_rel_user')]
#[ORM\Index(name: 'course', columns: ['c_id'])]
#[ORM\Entity]
class CGroupRelUser
{
    use UserTrait;

    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\Column(name: 'c_id', type: 'integer')]
    protected int $cId;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'courseGroupsAsMember')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: CGroup::class, inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'iid', nullable: false, onDelete: 'CASCADE')]
    protected CGroup $group;

    #[ORM\Column(name: 'status', type: 'integer', nullable: false)]
    protected int $status;

    #[ORM\Column(name: 'role', type: 'string', length: 50, nullable: false)]
    protected string $role;

    #[ORM\Column(name: 'ready_autogroup', type: 'boolean', options: [
        'default' => 0,
    ])]
    protected bool $readyAutogroup = false;

    public function setGroup(CGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return CGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getReadyAutogroup(): bool
    {
        return $this->readyAutogroup;
    }

    public function setReadyAutogroup(bool $readyAutogroup): self
    {
        $this->readyAutogroup = $readyAutogroup;

        return $this;
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set cId.
     *
     * @return CGroupRelUser
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
}
