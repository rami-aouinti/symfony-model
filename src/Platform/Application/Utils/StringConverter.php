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

namespace App\Platform\Application\Utils;

use DateTime;
use Exception;
use InvalidArgumentException;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-22)
 * @since 0.1.1 (2022-11-22) Add PHP Magic Number Detector (PHPMND).
 * @since 0.1.0 (2022-05-07) First version.
 * @package App\Utils
 */
class StringConverter
{
    final public const int PRECISION_NONE = -1;

    /**
     * Returns the calculated value.
     *
     * @throws Exception
     */
    public static function calculate(string $value, int $precision = self::PRECISION_NONE): int|float
    {
        $matches = [];
        if (!preg_match('~([\-]?[0-9]+)([/])([0-9]+)+~', $value, $matches)) {
            return intval($value);
        }

        return match ($matches[2]) {
            '/' => $precision === self::PRECISION_NONE ? intval($matches[1]) / (intval(intval($matches[3]) === 0 ? 1 : $matches[3])) : round(intval($matches[1]) / (intval(intval($matches[3]) === 0 ? 1 : $matches[3])), $precision),
            default => throw new Exception(sprintf('Unsupported calculation "%s" (%s:%d).', $matches[2], __FILE__, __LINE__)),
        };
    }

    /**
     * Optimizes "10/400" strings.
     *
     * @throws Exception
     */
    public static function optimizeSlashString(mixed $value): string
    {
        if (!is_string($value)) {
            return strval($value);
        }

        $matches = [];
        if (!preg_match('~([\-]?[0-9]+)([/])([0-9]+)+~', $value, $matches)) {
            return $value;
        }

        if ($matches[2] !== '/') {
            return $value;
        }

        $value1 = strval($matches[1]);
        $value2 = strval($matches[3]);

        while (str_ends_with($value1, '0') && str_ends_with($value2, '0')) {
            $value1 = substr($value1, 0, -1);
            $value2 = substr($value2, 0, -1);
        }

        return sprintf('%s/%s', $value1, $value2);
    }

    /**
     * Converts given date time string into a DateTime object.
     */
    public static function convertDateTime(string $dateTimeString): DateTime
    {
        $dateParsed = date_parse($dateTimeString);

        if (!array_key_exists('error_count', $dateParsed)) {
            throw new InvalidArgumentException(sprintf('Missing key "%s" (%s:%d).', 'error_count', __FILE__, __LINE__));
        }

        foreach (['year', 'month', 'day', 'hour', 'minute', 'second', 'error_count', 'warning_count'] as $name) {
            if (!array_key_exists($name, $dateParsed)) {
                throw new InvalidArgumentException(sprintf('Missing key "%s" (%s:%d).', $name, __FILE__, __LINE__));
            }
        }

        if ($dateParsed['error_count'] > 0) {
            throw new InvalidArgumentException(sprintf('Unable to parse given date "%s" (%s:%d).', $dateTimeString, __FILE__, __LINE__));
        }

        $dateTime = new DateTime();
        $dateTime->setDate(intval($dateParsed['year']), intval($dateParsed['month']), intval($dateParsed['day']));

        if (!empty($dateParsed['hour']) && !empty($dateParsed['minute']) && !empty($dateParsed['second'])) {
            $dateTime->setTime(intval($dateParsed['hour']), intval($dateParsed['minute']), intval($dateParsed['second']));
        }

        return $dateTime;
    }

    /**
     * Converts given date time string into a string.
     */
    public static function convertDateTimeFormat(string $dateTimeString, string $format): string
    {
        return self::convertDateTime($dateTimeString)->format($format);
    }
}
