<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\User\Application\Service;

use App\User\Domain\Entity\User;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-26)
 * @package App\Service
 */
class SecurityService
{
    public function __construct(
        protected Security $security
    ) {
    }

    /**
     * Check if Security class is loaded and user is logged in.
     */
    public function isLoaded(): bool
    {
        return $this->security->getToken() instanceof TokenInterface;
    }

    /**
     * Returns the Security class.
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * Returns if User is logged in.
     */
    public function isUserLoggedIn(): bool
    {
        return $this->security->getUser() instanceof User;
    }

    /**
     * Returns User entity.
     *
     * @throws Exception
     */
    public function getUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new Exception(sprintf('Expect user class (%s:%d)', __FILE__, __LINE__));
        }

        return $user;
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     */
    public function isGranted(string $attribute, mixed $subject = null): bool
    {
        return $this->security->isGranted($attribute, $subject);
    }

    /**
     * Checks if user is an admin.
     */
    public function isGrantedByAnAdmin(): bool
    {
        return $this->isGrantedOr(User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN);
    }

    /**
     * Checks that at least one given role is granted.
     */
    public function isGrantedOr(): bool
    {
        /** @var string[] $attributes */
        $attributes = func_get_args();

        foreach ($attributes as $attribute) {
            if ($this->isGranted($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks that all given roles are granted.
     */
    public function isGrantedAnd(): bool
    {
        /** @var string[] $attributes */
        $attributes = func_get_args();

        foreach ($attributes as $attribute) {
            if (!$this->isGranted($attribute)) {
                return false;
            }
        }

        return true;
    }
}
