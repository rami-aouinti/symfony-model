<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Entity\User\UserRelUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'CREATE'|'VIEW'|'EDIT'|'DELETE', UserRelUser>
 */
class UserRelUserVoter extends Voter
{
    public const CREATE = 'CREATE';
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    public function __construct(
        private readonly Security $security
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        $options = [
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];

        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, $options, true)) {
            return false;
        }

        // only vote on Post objects inside this voter
        return $subject instanceof UserRelUser;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Admins have access to everything.
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var UserRelUser $userRelUser */
        $userRelUser = $subject;

        switch ($attribute) {
            case self::CREATE:
                if ($userRelUser->getUser() === $user) {
                    return true;
                }

                break;
            case self::EDIT:
                if ($userRelUser->getUser() === $user) {
                    return true;
                }

                if (
                    $userRelUser->getFriend() === $user
                    && $userRelUser->getRelationType() === UserRelUser::USER_RELATION_TYPE_FRIEND_REQUEST
                ) {
                    return true;
                }

                break;
            case self::VIEW:
                return true;
            case self::DELETE:
                if ($userRelUser->getUser() === $user) {
                    return true;
                }

                if ($userRelUser->getFriend() === $user) {
                    return true;
                }

                break;
        }

        return false;
    }
}
