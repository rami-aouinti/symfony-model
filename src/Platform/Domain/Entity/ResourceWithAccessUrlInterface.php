<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\Access\Domain\Entity\AccessUrl;
use Doctrine\Common\Collections\Collection;

interface ResourceWithAccessUrlInterface
{
    public function addAccessUrl(?AccessUrl $url): self;

    /**
     * @return Collection<int, EntityAccessUrlInterface>
     */
    public function getUrls(): Collection;
}
