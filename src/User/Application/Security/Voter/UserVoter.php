<?php

declare(strict_types=1);

namespace App\User\Application\Security\Voter;

use App\Configuration\Infrastructure\Repository\SettingsRepository;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

use function array_key_exists;
use function in_array;

/**
 * @package App\User\Application\Security\Voter
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserVoter extends Voter
{
    public const string USE_HTML = 'USE_HTML';

    public function __construct(
        private readonly SettingsRepository $repository
    ) {
    }

    protected function supports(string $attribute, $subject = null): bool
    {
        return $attribute === self::USE_HTML;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::USE_HTML => $this->canUseHtml($user),
            default => throw new LogicException('This code should not be reached!'),
        };
    }

    private function canUseHtml(UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $settings = $this->repository->findAllAsArray();

        if (!array_key_exists('allow_html', $settings)) {
            return false;
        }

        return $settings['allow_html'] === '1';
    }
}
