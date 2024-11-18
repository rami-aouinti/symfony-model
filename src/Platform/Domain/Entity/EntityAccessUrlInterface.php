<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\Access\Domain\Entity\AccessUrl;

interface EntityAccessUrlInterface
{
    public function setUrl(?AccessUrl $url): self;

    public function getUrl(): ?AccessUrl;
}
