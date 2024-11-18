<?php

declare(strict_types=1);

namespace App\Platform\Application\Utils;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
