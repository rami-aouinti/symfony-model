<?php

declare(strict_types=1);

namespace App\Property\Application\Service;

use App\Property\Application\Service\Cache\ClearCache;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @package App\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class AbstractService
{
    use ClearCache;

    private readonly FlashBagAwareSessionInterface $session;

    public function __construct(
        private readonly CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack
    ) {
        $this->session = $requestStack->getSession();
    }

    /**
     * Checks the validity of a CSRF token.
     */
    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->tokenManager->isTokenValid(new CsrfToken($id, $token));
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @throws \LogicException
     */
    protected function addFlash(string $type, string $message): void
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}
