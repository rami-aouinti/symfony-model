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

use App\Platform\Transport\Exception\KeyNotFoundException;
use App\Platform\Transport\Exception\TypeInvalidException;
use App\Tests\Unit\Utils\Checker\CheckerArrayTest;

use function App\Calendar\Application\Utils\Checker\gettype;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 * @link CheckerArrayTest
 */
class CheckerArray extends CheckerAbstract
{
    /**
     * Checks the given index and return as string.
     *
     * @throws TypeInvalidException
     * @throws KeyNotFoundException
     */
    public function checkIndex(string $index): mixed
    {
        if (!is_array($this->value)) {
            throw new TypeInvalidException('iterable', gettype($this->value));
        }

        if (!array_key_exists($index, $this->value)) {
            throw new KeyNotFoundException($index);
        }

        return $this->value[$index];
    }

    /**
     * Checks the given index and return as string.
     *
     * @throws TypeInvalidException
     * @throws KeyNotFoundException
     */
    public function checkIndexString(string $index): string
    {
        $value = $this->checkIndex($index);

        if (!is_string($value)) {
            throw new TypeInvalidException('string');
        }

        return $value;
    }

    /**
     * Checks the given index and return as string or false (null).
     *
     * @throws KeyNotFoundException
     * @throws TypeInvalidException
     */
    public function checkIndexStringOrFalse(string $index): string|false
    {
        $value = $this->checkIndex($index);

        if (!is_string($value) && $value !== null) {
            throw new TypeInvalidException('string || null');
        }

        if ($value === null) {
            return false;
        }

        return $value;
    }

    /**
     * Checks the given index and return as array.
     *
     * @return array<int, mixed>
     * @throws TypeInvalidException
     * @throws KeyNotFoundException
     */
    public function checkIndexArray(string $index): array
    {
        $value = $this->checkIndex($index);

        if (!is_array($value)) {
            throw new TypeInvalidException('array');
        }

        return $value;
    }

    /**
     * Checks the given index and return as string array.
     *
     * @return string[]
     * @throws TypeInvalidException
     * @throws KeyNotFoundException
     */
    public function checkIndexStringArray(string $index): array
    {
        $value = $this->checkIndex($index);

        if (!is_array($value)) {
            throw new TypeInvalidException('array');
        }

        foreach ($value as $string) {
            if (!is_string($string)) {
                throw new TypeInvalidException('string');
            }
        }

        return $value;
    }

    /**
     * Checks the given index and return as integer.
     *
     * @throws TypeInvalidException
     * @throws KeyNotFoundException
     */
    public function checkIndexInteger(string $index): int
    {
        $value = $this->checkIndex($index);

        if (!is_int($value)) {
            throw new TypeInvalidException('array');
        }

        return $value;
    }
}
