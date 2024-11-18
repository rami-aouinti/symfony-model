<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Access\Domain\Entity;

use App\Platform\Domain\Entity\EntityAccessUrlInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\Mapping as ORM;

/**
 * AccessUrlRelSession.
 */
#[ORM\Table(name: 'access_url_rel_session')]
#[ORM\Entity]
class AccessUrlRelSession implements EntityAccessUrlInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, cascade: ['persist'], inversedBy: 'urls')]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id')]
    protected ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: AccessUrl::class, cascade: ['persist'], inversedBy: 'sessions')]
    #[ORM\JoinColumn(name: 'access_url_id', referencedColumnName: 'id')]
    protected ?AccessUrl $url = null;

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUrl(?AccessUrl $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): ?AccessUrl
    {
        return $this->url;
    }

    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
