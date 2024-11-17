<?php

declare(strict_types=1);

namespace App\Role\Domain\Entity;

use App\Platform\Domain\Entity\Traits\Timestampable;
use App\Platform\Domain\Entity\Traits\Uuid;
use App\User\Domain\Entity\Traits\Blameable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @package App\Role\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'platform_roles_permissions')]
#[ORM\UniqueConstraint(name: 'role_permission', columns: ['role_id', 'permission'])]
#[ORM\Entity(repositoryClass: 'App\Role\Infrastructure\Repository\RolePermissionRepository')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[UniqueEntity(['role', 'permission'])]
class RolePermission
{
    use Timestampable;
    use Uuid;
    use Blameable;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'RolePermission',
        'RolePermission.id',
    ])]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Role $role = null;
    #[ORM\Column(name: 'permission', type: 'string', length: 50, nullable: false)]
    #[Assert\Length(max: 50)]
    private ?string $permission = null;
    #[ORM\Column(name: 'allowed', type: 'boolean', nullable: false, options: [
        'default' => false,
    ])]
    #[Assert\NotNull]
    private bool $allowed = false;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Alias for isValue()
     */
    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;

        return $this;
    }
}
