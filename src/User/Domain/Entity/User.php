<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use App\Platform\Domain\Entity\EntityInterface;
use App\Platform\Domain\Entity\Traits\ColorTrait;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\Platform\Domain\Entity\Traits\Uuid;
use App\User\Application\Voter\UserVoter;
use App\User\Domain\Entity\Traits\Blameable;
use App\User\Domain\Entity\Traits\TwoFactorTrait;
use App\User\Infrastructure\Repository\UserRepository;
use App\User\Transport\EventListener\Entity\UserListener;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Random\RandomException;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Stringable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @package App\Entity
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'platform_user')]
#[ORM\EntityListeners([UserListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        # Security filter for collection operations at App\Doctrine\CurrentUserExtension
        new GetCollection(
            normalizationContext: [
                'groups' => ['user'],
            ]
        ),
        # Security filter for collection operations at App\Doctrine\CurrentUserExtension
        new GetCollection(
            uriTemplate: '/users/extended.{_format}',
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['user_extended'],
            ]
        ),
        new Post(
            normalizationContext: [
                'groups' => ['user'],
            ],
            securityPostDenormalize: 'is_granted("' . UserVoter::ATTRIBUTE_USER_POST . '")',
            securityPostDenormalizeMessage: 'Only admins can add users.'
        ),

        new Delete(
            normalizationContext: [
                'groups' => ['user'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_USER_DELETE . '", object)',
            securityMessage: 'Only own users can be deleted.'
        ),
        new Get(
            normalizationContext: [
                'groups' => ['user'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_USER_GET . '", object)',
            securityMessage: 'Only own users can be read.'
        ),
        new Get(
            uriTemplate: '/users/{id}/extended.{_format}',
            uriVariables: [
                'id',
            ],
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Retrieves the collection of extended Event resources.'
                    ),
                ],
                summary: 'Retrieves the collection of extended Event resources.',
            ),
            normalizationContext: [
                'groups' => ['user_extended'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_USER_GET . '", object)',
            securityMessage: 'Only own users can be read.'
        ),
        new Patch(
            normalizationContext: [
                'groups' => ['user'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_USER_PATCH . '", object)',
            securityMessage: 'Only own users can be modified.'
        ),
        new Put(
            normalizationContext: [
                'groups' => ['user'],
            ],
            security: 'is_granted("' . UserVoter::ATTRIBUTE_USER_PUT . '", object)',
            securityMessage: 'Only own users can be modified.'
        ),
    ],
    normalizationContext: [
        'enable_max_depth' => true,
        'groups' => ['user'],
    ],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityInterface, Stringable, TwoFactorInterface
{
    use Timestampable;
    use Uuid;
    use ColorTrait;
    use Blameable;
    use TwoFactorTrait;

    /**
     * Requests older than this many seconds will be considered expired.
     */
    final public const int RETRY_TTL = 3600;
    /**
     * Maximum time that the confirmation token will be valid.
     */
    final public const int TOKEN_TTL = 43200;

    final public const string ROLE_USER = 'ROLE_USER';
    final public const string ROLE_ADMIN = 'ROLE_ADMIN';
    final public const string ROLE_SUPER_ADMIN = 'ROLE_ADMIN';
    final public const string API_ENDPOINT_COLLECTION = '/api/v1/users';

    final public const string API_ENDPOINT_ITEM = '/api/v1/users/%d';

    final public const string PASSWORD_UNCHANGED = '**********';

    final public const int SHORT_HASH_LENGTH = 8;

    final public const array CRUD_FIELDS_ADMIN = ['id'];

    final public const array CRUD_FIELDS_REGISTERED = ['id', 'idHash', 'email', 'username', 'password', 'plainPassword', 'firstname', 'lastname', 'roles', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_INDEX = ['id', 'idHash', 'email', 'username', 'password', 'firstname', 'lastname', 'roles', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_NEW = ['id', 'email', 'username', 'plainPassword', 'firstname', 'lastname', 'roles'];

    final public const array CRUD_FIELDS_EDIT = self::CRUD_FIELDS_NEW;

    final public const array CRUD_FIELDS_DETAIL = ['id', 'idHash', 'email', 'username', 'password', 'firstname', 'lastname', 'roles', 'updatedAt', 'createdAt'];

    final public const array CRUD_FIELDS_FILTER = ['email', 'username', 'firstname', 'lastname'];

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'User',
        'User.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(name: 'id_hash', type: 'string', length: 40, unique: true, nullable: false)]
    private ?string $idHash = null;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user', 'user_extended'])]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user', 'user_extended'])]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Profile::class, cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $confirmation_token;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?DateTimeInterface $password_requested_at;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?DateTime $emailVerifiedAt;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    /**
     * @return array{int|null, string|null, string|null}
     */
    public function __serialize(): array
    {
        return [$this->id, $this->username, $this->password];
    }

    /**
     * @param array{int|null, string, string} $data
     */
    public function __unserialize(array $data): void
    {
        [$this->id, $this->username, $this->password] = $data;
    }

    /**
     * __toString method.
     */
    #[Pure]
    public function __toString(): string
    {
        return $this->getFullName();
    }

    /**
     * Returns the config of user.
     *
     * @return string[]
     * @throws Exception
     */
    #[ArrayShape([
        'fullName' => 'string',
        'roleI18n' => 'string',
    ])]
    public function getConfig(): array
    {
        $roleI18n = match (true) {
            in_array(self::ROLE_SUPER_ADMIN, $this->roles) => 'admin.user.fields.roles.entries.roleSuperAdmin',
            in_array(self::ROLE_ADMIN, $this->roles) => 'admin.user.fields.roles.entries.roleAdmin',
            in_array(self::ROLE_USER, $this->roles), $this->roles === [] => 'admin.user.fields.roles.entries.roleUser',
            default => throw new Exception(sprintf('Unknown role (%s:%d).', __FILE__, __LINE__)),
        };

        return [
            'fullName' => sprintf('%s %s', $this->firstname, $this->lastname),
            'roleI18n' => $roleI18n,
        ];
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * Gets the hash id of this user.
     *
     * @throws RandomException
     */
    public function getIdHash(): string
    {
        return $this->idHash ?? $this->getIdHashNew();
    }

    /**
     * Gets the hash id of this user.
     *
     * @throws RandomException
     */
    public function getIdHashShort(): string
    {
        return substr($this->getIdHash(), 0, self::SHORT_HASH_LENGTH);
    }

    /**
     * Gets the hash id of this user.
     *
     * @throws RandomException
     */
    public function getIdHashNew(): string
    {
        return sha1(random_int(1_000_000, 9_999_999) . random_int(1_000_000, 9_999_999));
    }

    /**
     * Sets the hash id of this user.
     *
     * @throws RandomException
     * @return $this
     */
    public function setIdHash(?string $idHash = null): self
    {
        $this->idHash = $idHash ?? $this->getIdHashNew();

        return $this;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Gets the firstname of this user.
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Sets the firstname of this user.
     *
     * @return $this
     */
    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Gets the lastname of this user.
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Sets the lastname of this user.
     *
     * @return $this
     */
    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = self::ROLE_USER;
        }

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }

    /**
     * Sets automatically the hash id of this user.
     *
     * @throws RandomException
     * @return $this
     */
    #[ORM\PrePersist]
    public function setIdHashAutomatically(): self
    {
        if ($this->idHash === null) {
            $this->setIdHash(sha1(sprintf('salt_%d_%d', random_int(0, 999_999_999), random_int(0, 999_999_999))));
        }

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    public function setConfirmationToken(?string $confirmation_token): self
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getPasswordRequestedAt(): ?DateTimeInterface
    {
        return $this->password_requested_at;
    }

    public function setPasswordRequestedAt(?DateTimeInterface $password_requested_at): self
    {
        $this->password_requested_at = $password_requested_at;

        return $this;
    }

    /**
     * Checks whether the password reset request has expired.
     */
    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof DateTime
            && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function isVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function getEmailVerifiedAt(): ?DateTime
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?DateTime $dateTime): self
    {
        $this->emailVerifiedAt = $dateTime;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }
    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }
}
