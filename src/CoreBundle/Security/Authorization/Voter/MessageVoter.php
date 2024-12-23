<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\User\User;
use App\Message\Domain\Entity\Message;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'CREATE'|'VIEW'|'EDIT'|'DELETE', Message>
 */
class MessageVoter extends Voter
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
        return $subject instanceof Message;
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

        /** @var Message $message */
        $message = $subject;

        switch ($attribute) {
            case self::CREATE:
            case self::EDIT:
                if ($message->getSender() === $user) {
                    return true;
                }

                break;
            case self::VIEW:
                if ($message->hasUserReceiver($user) || $message->getSender() === $user) {
                    return true;
                }

                break;
            case self::DELETE:
                // @todo
                break;
                /*if ($message->hasReceiver($user) &&
                    Message::MESSAGE_TYPE_INBOX === $message->getMsgType()
                ) {
                    return true;
                }*/

                // User cannot delete.
                /*if ($message->getSender() === $user && Message::MESSAGE_TYPE_OUTBOX === $message->getMsgType()) {
                    return true;
                }*/
        }

        return false;
    }
}
