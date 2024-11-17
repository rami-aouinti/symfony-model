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

namespace App\Place\Infrastructure\DataType;

use App\Platform\Application\Utils\GPSConverter;
use Exception;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-04-22)
 * @package App\DataType
 */
class GPSPosition
{
    final public const int SECONDS_PER_HOUR = 3600;

    final public const string FORMAT_DMS_SHORT_1 = '%d°%d′%s″%s';

    final public const string FORMAT_DMS_SHORT_2 = '%s%d°%d′%s″';
    /**
     * @var array<string, int|float|string|null>
     */
    protected array $data;

    /**
     * @param array<string, int|float|string> $data
     * @throws Exception
     */
    public function __construct(array $data, ?string $direction = null)
    {
        assert(array_key_exists('degree', $data));
        assert(array_key_exists('minutes', $data));
        assert(array_key_exists('seconds', $data));

        if ($direction !== null) {
            $data['direction'] = $direction;
        }

        if (array_key_exists('direction', $data)) {
            $data['type'] = GPSConverter::getType(strval($data['direction']));
        } else {
            $data = array_merge(
                $data,
                [
                    'type' => null,
                    'direction' => null,
                ]
            );
        }

        $this->data = $data;
    }

    /**
     * Returns the data object.
     *
     * @return array<string, int|float|string|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the degree of gps position.
     */
    public function getDegree(): int
    {
        return intval($this->data['degree']);
    }

    /**
     * Returns the minutes of gps position.
     */
    public function getMinutes(): int
    {
        return intval($this->data['minutes']);
    }

    /**
     * Returns the minutes of gps position.
     */
    public function getSeconds(): float
    {
        return floatval($this->data['seconds']);
    }

    /**
     * Returns the direction of gps position.
     */
    public function getDirection(): ?string
    {
        if ($this->data['direction'] === null) {
            return null;
        }

        return strval($this->data['direction']);
    }

    /**
     * Returns the type of gps position.
     */
    public function getType(): ?string
    {
        if ($this->data['type'] === null) {
            return null;
        }

        return strval($this->data['type']);
    }

    /**
     * Returns dms of gps position.
     *
     * @throws Exception
     */
    public function getDms(string $format = self::FORMAT_DMS_SHORT_1): string
    {
        return match ($format) {
            self::FORMAT_DMS_SHORT_1 => sprintf($format, $this->getDegree(), $this->getMinutes(), $this->getSeconds(), $this->getDirection()),
            self::FORMAT_DMS_SHORT_2 => sprintf($format, $this->getDirection(), $this->getDegree(), $this->getMinutes(), $this->getSeconds()),
            default => throw new Exception(sprintf('Unknown format "%s" given (%s:%d).', $format, __FILE__, __LINE__)),
        };
    }

    /**
     * Return decimal degree.
     */
    public function getDecimalDegree(): float
    {
        $multiplication = in_array($this->getDirection(), [GPSConverter::DIRECTION_SOUTH, GPSConverter::DIRECTION_WEST]) ? -1 : 1;

        return $multiplication * round($this->getDegree() + ((($this->getMinutes() * 60) + $this->getSeconds()) / self::SECONDS_PER_HOUR), 6);
    }
}
