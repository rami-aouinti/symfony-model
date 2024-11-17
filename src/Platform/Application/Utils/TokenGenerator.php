<?php

declare(strict_types=1);

namespace App\Platform\Application\Utils;

use Random\RandomException;

/**
 * Class TokenGenerator
 *
 * @package App\Platform\Application\Utils
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class TokenGenerator
{
    /**
     * @throws RandomException
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
