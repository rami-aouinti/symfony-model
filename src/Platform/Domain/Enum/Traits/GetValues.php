<?php

declare(strict_types=1);

namespace App\Platform\Domain\Enum\Traits;

use function array_column;

/**
 * @package App\Platform
 */
trait GetValues
{
    /**
     * @return array<int, string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
