<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\Xapi\Domain\Entity;

use App\CoreBundle\Repository\XApiExtensionsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package App\CoreBundle\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: XApiExtensionsRepository::class)]
class XApiExtensions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $identifier = null;

    #[ORM\Column]
    private array $extensions = [];

    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(array $extensions): static
    {
        $this->extensions = $extensions;

        return $this;
    }
}
