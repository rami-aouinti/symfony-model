<?php

declare(strict_types=1);

namespace App\Platform\Application\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;

/**
 * @package App\Middleware
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class VerifyCsrfToken
{
    public function __construct(
        private CsrfTokenManagerInterface $tokenManager
    ) {
    }

    public function handle(Request $request): void
    {
        if (!$this->isCsrfTokenValid($this->getToken($request))) {
            throw new TokenNotFoundException('Sorry, your session has expired. Please refresh and try again.');
        }
    }

    private function isCsrfTokenValid(?string $token): bool
    {
        return $this->tokenManager->isTokenValid(new CsrfToken('csrf_token', $token));
    }

    /**
     * @return mixed|string|null
     */
    private function getToken(Request $request): mixed
    {
        return $request->headers->get('x-csrf-token')
            ?? $request->get('csrf_token');
    }
}
