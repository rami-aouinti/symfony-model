<?php

declare(strict_types=1);

namespace App\Platform\Domain\Doctrine\DBAL\Types;

use App\Platform\Domain\Enum\LogLogin;

/**
 * @package App\General
 */
class EnumLogLoginType extends EnumType
{
    protected static string $name = Types::ENUM_LOG_LOGIN;

    protected static string $enum = LogLogin::class;
}
