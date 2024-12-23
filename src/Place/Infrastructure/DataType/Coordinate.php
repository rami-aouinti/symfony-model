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

use Exception;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-04-22)
 * @package App\DataType
 */
class Coordinate
{
    final public const string TYPE_LATITUDE = 'latitude';

    final public const string TYPE_LONGITUDE = 'longitude';

    final public const string DIRECTION_NORTH = 'N';

    final public const string DIRECTION_SOUTH = 'S';

    final public const string DIRECTION_WEST = 'W';

    final public const string DIRECTION_EAST = 'E';

    final public const array UNIT_LATITUDE = [self::DIRECTION_NORTH, self::DIRECTION_SOUTH];

    final public const array UNIT_LONGITUDE = [self::DIRECTION_WEST, self::DIRECTION_EAST];
    /* X Place */
    protected GPSPosition $longitude;

    /* Y Place */
    protected GPSPosition $latitude;

    public function __construct(GPSPosition $longitude, GPSPosition $latitude)
    {
        assert($longitude->getType() === self::TYPE_LONGITUDE);
        assert($latitude->getType() === self::TYPE_LATITUDE);

        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    /**
     * Returns the longitude position.
     */
    public function getLongitude(): GPSPosition
    {
        return $this->longitude;
    }

    /**
     * Returns the latitude position.
     */
    public function getLatitude(): GPSPosition
    {
        return $this->latitude;
    }

    /**
     * Returns the position of longitude and latitude (float).
     *
     * @return float[]
     */
    #[ArrayShape([
        'longitude' => 'float',
        'latitude' => 'float',
    ])]
    public function getDecimalDegree(): array
    {
        return [
            'longitude' => $this->getLongitude()->getDecimalDegree(),
            'latitude' => $this->getLatitude()->getDecimalDegree(),
        ];
    }

    /**
     * Returns the position of longitude and latitude (string).
     *
     * @return string[]
     * @throws Exception
     */
    #[ArrayShape([
        'longitude' => 'string',
        'latitude' => 'string',
    ])]
    public function getDms(string $format = GPSPosition::FORMAT_DMS_SHORT_1): array
    {
        return [
            'longitude' => $this->getLongitude()->getDms($format),
            'latitude' => $this->getLatitude()->getDms($format),
        ];
    }

    /**
     * Returns the google string of longitude and latitude (string).
     *
     * @throws Exception
     */
    public function getGoogle(bool $asDms = false): string
    {
        if ($asDms) {
            return sprintf('https://www.google.de/maps/place/%s+%s', $this->getLongitude()->getDms(), $this->getLatitude()->getDms());
        }

        return sprintf('https://www.google.de/maps/place/%f+%f', $this->getLongitude()->getDecimalDegree(), $this->getLatitude()->getDecimalDegree());
    }

    /**
     * Returns the openstreetmap link of longitude and latitude (string).
     */
    public function getOpenstreetmap(int $zoom = 14, string $layers = 'M'): string
    {
        return sprintf(
            'https://www.openstreetmap.org/?lat=%f&lon=%f&mlat=%f&mlon=%f&zoom=%d&layers=%s',
            $this->getLongitude()->getDecimalDegree(),
            $this->getLatitude()->getDecimalDegree(),
            $this->getLongitude()->getDecimalDegree(),
            $this->getLatitude()->getDecimalDegree(),
            $zoom,
            $layers
        );
    }
}
