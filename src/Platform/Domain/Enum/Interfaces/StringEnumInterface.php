<?php

declare(strict_types=1);

namespace App\Platform\Domain\Enum\Interfaces;

use BackedEnum;

/**
 * Enum StringEnumInterface
 *
 * @package App\Platform
 */
interface StringEnumInterface extends BackedEnum
{
    /**
     * @return array<int, string>
     */
    public static function getValues(): array;
}
