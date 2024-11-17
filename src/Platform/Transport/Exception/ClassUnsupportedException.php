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

use App\Platform\Transport\Exception\Base\BaseClassException;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
final class ClassUnsupportedException extends BaseClassException
{
    public const string TEXT_PLACEHOLDER = 'Unsupported class "%s" given.';

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $messageNonVerbose = sprintf(self::TEXT_PLACEHOLDER, $className);

        parent::__construct($messageNonVerbose);
    }
}
