<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Admin\Domain\Entity;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Admin list.
 */
#[ORM\Table(name: 'admin')]
#[ORM\Entity]
class Admin
{
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'admin', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
