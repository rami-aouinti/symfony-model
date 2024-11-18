<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Access\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use App\Platform\Domain\Entity\EntityAccessUrlInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * Class AccessUrlRelUser
 *
 * @package App\Access\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'access_url_rel_user')]
#[ORM\Index(columns: ['user_id'], name: 'idx_access_url_rel_user_user')]
#[ORM\Index(columns: ['access_url_id'], name: 'idx_access_url_rel_user_access_url')]
#[ORM\Index(columns: ['user_id', 'access_url_id'], name: 'idx_access_url_rel_user_access_url_user')]
#[ORM\Entity]
#[ApiResource(
    security: "is_granted('ROLE_ADMIN')"
)]
class AccessUrlRelUser implements EntityAccessUrlInterface, Stringable
{
    use UserTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'portals')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: AccessUrl::class, cascade: ['persist'], inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'access_url_id', referencedColumnName: 'id')]
    protected ?AccessUrl $url;

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): ?AccessUrl
    {
        return $this->url;
    }

    public function setUrl(?AccessUrl $url): self
    {
        $this->url = $url;

        return $this;
    }
}
