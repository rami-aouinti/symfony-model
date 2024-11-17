<?php

declare(strict_types=1);

namespace App\Platform\Domain\Doctrine\DBAL\Types;

use App\Platform\Domain\Enum\Language;

/**
 * @package App\General
 */
class EnumLanguageType extends EnumType
{
    protected static string $name = Types::ENUM_LANGUAGE;
    protected static string $enum = Language::class;
}
