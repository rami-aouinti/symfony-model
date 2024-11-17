<?php

declare(strict_types=1);

namespace App\Platform\Domain\Entity\Traits;

use App\Constants;
use App\Platform\Application\Export\Annotation as Exporter;
use App\Platform\Application\Validator\Constraints as Constraints;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait ColorTrait
{
    /**
     * The assigned color in HTML hex format, e.g. #dd1d00
     */
    #[ORM\Column(name: 'color', type: 'string', length: 7, nullable: true)]
    #[Serializer\Expose]
    #[Serializer\Groups(['Default'])]
    #[Exporter\Expose(label: 'color')]
    #[Constraints\HexColor]
    private ?string $color = null;

    public function getColor(): ?string
    {
        if ($this->color === Constants::DEFAULT_COLOR) {
            return null;
        }

        return $this->color;
    }

    public function hasColor(): bool
    {
        return $this->color !== null && $this->color !== Constants::DEFAULT_COLOR;
    }

    public function setColor(?string $color = null): void
    {
        $this->color = $color;
    }
}
