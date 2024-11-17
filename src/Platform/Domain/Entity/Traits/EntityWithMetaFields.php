<?php

declare(strict_types=1);

namespace App\Platform\Domain\Entity\Traits;

use Doctrine\Common\Collections\Collection;

interface EntityWithMetaFields
{
    /**
     * @return Collection|MetaTableTypeInterface[]
     */
    public function getMetaFields(): Collection;

    public function getMetaField(string $name): ?MetaTableTypeInterface;

    public function setMetaField(MetaTableTypeInterface $meta): self;
}
