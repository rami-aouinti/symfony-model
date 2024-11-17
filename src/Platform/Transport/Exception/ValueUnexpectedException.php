<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Platform\Transport\Exception;

use App\Platform\Transport\Exception\Base\BaseValueException;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
final class ValueUnexpectedException extends BaseValueException
{
    public const TEXT_PLACEHOLDER = 'Unexpected value "%s" given (%s).';

    public function __construct(string $value, string $message)
    {
        $messageNonVerbose = sprintf(self::TEXT_PLACEHOLDER, $value, $message);

        parent::__construct($messageNonVerbose);
    }
}
