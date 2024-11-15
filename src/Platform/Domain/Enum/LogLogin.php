<?php

declare(strict_types=1);

namespace App\Platform\Domain\Enum;

use App\Platform\Domain\Enum\Interfaces\DatabaseEnumInterface;
use App\Platform\Domain\Enum\Traits\GetValues;

/**
 * @package App\Log
 */
enum LogLogin: string implements DatabaseEnumInterface
{
    use GetValues;

    case FAILURE = 'failure';
    case SUCCESS = 'success';
}
