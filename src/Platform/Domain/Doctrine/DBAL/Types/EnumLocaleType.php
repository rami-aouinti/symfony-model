<?php

declare(strict_types=1);

namespace App\Platform\Domain\Doctrine\DBAL\Types;

use App\Platform\Domain\Enum\Locale;

/**
 * @package App\Platform
 */
class EnumLocaleType extends EnumType
{
    protected static string $name = Types::ENUM_LOCALE;
    protected static string $enum = Locale::class;
}
