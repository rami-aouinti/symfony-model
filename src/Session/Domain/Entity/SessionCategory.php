<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Session\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Access\Domain\Entity\AccessUrl;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SessionCategory
 *
 * @package App\Session\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ApiResource(operations: [new Get(), new Put(), new Patch(), new Delete(), new GetCollection(security: 'is_granted(\'ROLE_USER\')'), new Post(security: 'is_granted(\'ROLE_ADMIN\')')], normalizationContext: [
    'groups' => ['session_category:read'],
], denormalizationContext: [
    'groups' => ['session_category:write'],
], security: 'is_granted(\'ROLE_USER\')')]
#[ORM\Table(name: 'session_category')]
#[ORM\Entity]
class SessionCategory implements Stringable
{
    #[Groups(['session_category:read', 'session_rel_user:read'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;
    #[ORM\ManyToOne(targetEntity: AccessUrl::class, cascade: ['persist'], inversedBy: 'sessionCategories')]
    #[ORM\JoinColumn(name: 'access_url_id', referencedColumnName: 'id')]
    protected AccessUrl $url;
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Session::class)]
    protected Collection $sessions;
    #[Groups(['session_category:read', 'session_category:write', 'session:read', 'session_rel_user:read', 'user_subscriptions:sessions'])]
    #[Assert\NotBlank]
    #[ORM\Column(name: 'title', type: 'string', length: 100, unique: false, nullable: false)]
    protected string $title;
    #[ORM\Column(name: 'date_start', type: 'date', unique: false, nullable: true)]
    protected ?DateTime $dateStart = null;
    #[ORM\Column(name: 'date_end', type: 'date', unique: false, nullable: true)]
    protected ?DateTime $dateEnd = null;
    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }
    public function __toString(): string
    {
        return $this->title;
    }
    public function setUrl(AccessUrl $url): self
    {
        $this->url = $url;

        return $this;
    }
    public function getUrl(): AccessUrl
    {
        return $this->url;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function setDateStart(DateTime $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart.
     *
     * @return DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }
    public function setDateEnd(DateTime $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd.
     *
     * @return DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }
}
