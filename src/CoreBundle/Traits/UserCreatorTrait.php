<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

use App\CoreBundle\Entity\User\User;
use App\Platform\Domain\Entity\ResourceNode;

/**
 *
 */
trait UserCreatorTrait
{
    public ?ResourceNode $resourceNode = null;
    public ?User $resourceNodeCreator = null;

    public function getCreator(): ?User
    {
        if ($this->resourceNode === null) {
            return null;
        }

        if (!$this->resourceNode->hasCreator()) {
            return null;
        }

        return $this->resourceNode->getCreator();
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setCreator(User $user)
    {
        $this->resourceNodeCreator = $user;

        return $this;
    }

    public function getResourceNodeCreator(): ?User
    {
        return $this->resourceNodeCreator;
    }
}
