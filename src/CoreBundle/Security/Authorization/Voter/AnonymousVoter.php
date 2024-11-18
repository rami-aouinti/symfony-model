<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'ROLE_ANONYMOUS', User>
 */
class AnonymousVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'ROLE_ANONYMOUS';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user->getStatus() === User::ANONYMOUS;
    }
}
