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
    // Defining these constants is overkill for this simple application, but for real
    // applications, it's a recommended practice to avoid relying on "magic strings"
    public const string DELETE = 'delete';
    public const string EDIT = 'edit';
    public const string SHOW = 'show';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // this voter is only executed on Post objects and for three specific permissions
        return $subject instanceof Post && in_array($attribute, [self::SHOW, self::EDIT, self::DELETE], true);
    }

    /**
     * @param Post $post
     */
    protected function voteOnAttribute(string $attribute, $post, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // the user must be logged in; if not, deny permission
        if (!$user instanceof User) {
            return false;
        }

        // the logic of this voter is pretty simple: if the logged-in user is the
        // author of the given blog post, grant permission; otherwise, deny it.
        // (the supports() method guarantees that $post is a Post object)
        return $user === $post->getAuthor();
    }
}
