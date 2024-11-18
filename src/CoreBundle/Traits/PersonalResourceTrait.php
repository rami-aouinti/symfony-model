<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

/**
 *
 */
trait PersonalResourceTrait
{
    protected bool $loadPersonalResources = true;

    public function loadPersonalResources(): bool
    {
        return $this->loadPersonalResources;
    }
}
