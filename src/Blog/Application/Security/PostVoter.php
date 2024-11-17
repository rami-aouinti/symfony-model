<?php

declare(strict_types=1);

namespace App\Blog\Application\Security;

use App\Blog\Domain\Entity\Post;
use App\User\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function in_array;

/**
 * @package App\Security
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PostVoter extends Voter
{
    public const string DELETE = 'delete';
    public const string EDIT = 'edit';
    public const string SHOW = 'show';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Post && in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    /**
     * @param Post $post
     */
    protected function voteOnAttribute(string $attribute, $post, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user === $post->getAuthor();
    }
}
