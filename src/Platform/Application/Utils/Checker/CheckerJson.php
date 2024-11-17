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

namespace App\Platform\Application\Utils\Checker;

use App\Platform\Transport\Exception\TypeInvalidException;
use App\Tests\Unit\Utils\Checker\CheckerJsonTest;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 * @link CheckerJsonTest
 */
class CheckerJson extends CheckerAbstract
{
    /**
     * Checks the given value for json.
     *
     * @throws TypeInvalidException
     */
    public function checkJson(): string
    {
        $this->value = (new Checker($this->value))->checkString();

        if (!json_validate($this->value)) {
            throw new TypeInvalidException('json');
        }

        return $this->value;
    }

    /**
     * Checks the given value for json.
     */
    public function isJson(): bool
    {
        if (!is_string($this->value)) {
            return false;
        }

        return json_validate($this->value);
    }
}
