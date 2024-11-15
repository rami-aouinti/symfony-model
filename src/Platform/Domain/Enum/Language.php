<?php

declare(strict_types=1);

namespace App\Platform\Domain\Enum;

use App\Platform\Domain\Enum\Interfaces\DatabaseEnumInterface;
use App\Platform\Domain\Enum\Traits\GetValues;

/**
 * Language
 *
 * @package App\General
 */
enum Language: string implements DatabaseEnumInterface
{
    use GetValues;

    case EN = 'en';
    case RU = 'ru';
    case UA = 'ua';
    case FI = 'fi';

    public static function getDefault(): self
    {
        return self::EN;
    }
}
