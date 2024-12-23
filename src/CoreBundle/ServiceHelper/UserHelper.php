<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\ServiceHelper;

use App\CoreBundle\Entity\User\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserHelper
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function getCurrent(): ?User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        return $user instanceof UserInterface ? $user : null;
    }
}
