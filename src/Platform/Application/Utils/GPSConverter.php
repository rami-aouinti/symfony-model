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

use App\Place\Infrastructure\DataType\Coordinate;
use App\Place\Infrastructure\DataType\GPSPosition;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-22)
 * @since 0.1.1 (2022-11-22) Add PHP Magic Number Detector (PHPMND).
 * @since 0.1.0 (2022-04-22) First version.
 * @package App\Command
 */
class GPSConverter
{
    final public const ANGLE_360 = 360;

    final public const ANGLE_180 = 180;

    final public const ANGLE_90 = 90;

    final public const ANGLE_0 = 0;

    final public const ANGLE_360_0 = 360.;

    final public const ANGLE_180_0 = 180.;

    final public const ANGLE_157_5 = 157.5;

    final public const ANGLE_112_5 = 112.5;

    final public const ANGLE_90_0 = 90.;

    final public const ANGLE_67_5 = 67.5;

    final public const ANGLE_22_5 = 22.5;

    final public const ANGLE_0_0 = 0.;

    final public const REGEXP_ENCRYPTED_LATITUDE_LONGITUDE = '~!3d([0-9]+\.[0-9]+)+.+!4d([0-9]+\.[0-9]+)~';

    final public const REGEXP_GOOGLE_REDIRECT = '~(https://maps.app.goo.gl/[a-zA-Z0-9]+)$~';

    final public const REGEXP_GOOGLE_LOCATION_REDIRECT = '~^location: .+!3d([0-9]+\.[0-9]+)+.+!4d([0-9]+\.[0-9]+).+~m';

    final public const REGEXP_SPLIT_LATITUDE_LONGITUDE = '~[, ]+~';

    final public const REGEXP_DECIMAL = '[\-_]?\d+[.]\d+[°]*';

    final public const REGEXP_DMS = '(?:[NEOWS]?)[ ]?(?:\d+°)[ ]?(?:\d+′)[ ]?\d+(?:(?:.\d+)?″)[ ]?(?:[NEOWS]?)';

    final public const REGEXP_DMS_2 = '([NEOWS]?)[ ]?(\d+°)[ ]?(\d+′)[ ]?(\d+(?:.\d+)?″)[ ]?([NEOWS]?)';

    final public const UNIT_DEGREE = [
        '°',
    ];

    final public const UNIT_MINUTES = [
        '’',
        '\'',
        '′',
    ];

    final public const UNIT_SECONDS = [
        '\'’',
        '"',
        '″',
    ];

    final public const DIRECTION_NORTH = 'N';
    final public const DIRECTION_SOUTH = 'S';
    final public const DIRECTION_WEST = 'W';
    final public const DIRECTION_EAST = 'E';

    protected const REGEXP_VERSION_1 = 1;

    protected const REGEXP_VERSION_2 = 2;

    /**
     * Get type from direction.
     *
     * @throws Exception
     */
    public static function getType(string $direction): string
    {
        return match ($direction) {
            Coordinate::DIRECTION_SOUTH, Coordinate::DIRECTION_NORTH => Coordinate::TYPE_LATITUDE,
            Coordinate::DIRECTION_EAST, Coordinate::DIRECTION_WEST => Coordinate::TYPE_LONGITUDE,
            default => throw new Exception(sprintf('Unknown direction "%s" given (%s:%d).', $direction, __FILE__, __LINE__)),
        };
    }

    /**
     * Parses given dms string.
     *
     * @return array<string, int|float|string>
     * @throws Exception
     */
    #[ArrayShape([
        'degree' => 'int',
        'minutes' => 'int',
        'seconds' => 'float',
        'type' => 'string',
        'direction' => 'string',
    ])]
    public static function parseDms(string $dms): array
    {
        foreach (
            [
                Coordinate::TYPE_LATITUDE => Coordinate::UNIT_LATITUDE,
                Coordinate::TYPE_LONGITUDE => Coordinate::UNIT_LONGITUDE,
            ] as $type => $units
        ) {
            $matches = [];

            if (preg_match(self::getRegexp($units, self::REGEXP_VERSION_1), $dms, $matches)) {
                [, $degree, $minutes, $seconds, $direction] = $matches;

                return self::getData(intval($degree), intval($minutes), floatval($seconds), $direction);
            }

            if (preg_match(self::getRegexp($units, self::REGEXP_VERSION_2), $dms, $matches)) {
                [, $direction, $degree, $minutes, $seconds] = $matches;

                return self::getData(intval($degree), intval($minutes), floatval($seconds), $direction);
            }
        }

        throw new Exception(sprintf('Unable to parse dms string (%s:%d).', __FILE__, __LINE__));
    }

    /**
     * Converts given decimal degree into dms string.
     *
     * @return array<string, int|float|string>
     * @throws Exception
     */
    #[ArrayShape([
        'degree' => 'int',
        'minutes' => 'int',
        'seconds' => 'float',
    ])]
    public static function parseDecimalDegree(float $decimalDegree, ?string $direction = null): array
    {
        $degree = floor($decimalDegree);

        $secondsOverall = ($decimalDegree - $degree) * GPSPosition::SECONDS_PER_HOUR;

        $minutes = floor($secondsOverall / 60);

        $seconds = $secondsOverall - $minutes * 60;

        $data = [
            'degree' => intval($degree),
            'minutes' => intval($minutes),
            'seconds' => round(floatval($seconds), 6),
            'decimal' => $decimalDegree,
        ];

        if ($direction !== null) {
            $data = array_merge(
                $data,
                [
                    'type' => self::getType($direction),
                    'direction' => $direction,
                ]
            );
        }

        return $data;
    }

    /**
     * Converts given dms string into decimal degree.
     *
     * @throws Exception
     */
    public static function dms2DecimalDegree(string $dms, ?string $direction = null): float
    {
        $value = (new GPSPosition(self::parseDms($dms)))->getDecimalDegree();

        if (in_array($direction, [self::DIRECTION_WEST, self::DIRECTION_SOUTH])) {
            $value *= -1;
        }

        return $value;
    }

    /**
     * Converts given dms string into direction.
     *
     * @throws Exception
     */
    public static function dms2Direction(string $dms): ?string
    {
        return (new GPSPosition(self::parseDms($dms)))->getDirection();
    }

    /**
     * Converts given dms coordinates string into decimal degree.
     *
     * @return float[]
     * @throws Exception
     */
    #[ArrayShape([
        'longitude' => 'float',
        'latitude' => 'float',
    ])]
    public static function dms2DecimalDegrees(string $dmsLongitude, string $dmsLatitude): array
    {
        return (new Coordinate(new GPSPosition(self::parseDms($dmsLongitude)), new GPSPosition(self::parseDms($dmsLatitude))))->getDecimalDegree();
    }

    /**
     * Converts given decimal degree into dms.
     *
     * @throws Exception
     */
    public static function decimalDegree2dms(float $decimalDegree, ?string $direction = null, string $format = GPSPosition::FORMAT_DMS_SHORT_1): string
    {
        if ($direction !== null) {
            $decimalDegree = $decimalDegree < 0 ? -$decimalDegree : $decimalDegree;
        }

        return (new GPSPosition(self::parseDecimalDegree($decimalDegree), $direction))->getDms($format);
    }

    /**
     * Converts given decimal degree into dms.
     *
     * @return string[]
     * @throws Exception
     */
    #[ArrayShape([
        'longitude' => 'string',
        'latitude' => 'string',
    ])]
    public static function decimalDegree2dmss(float $decimalDegreeLongitude, float $decimalDegreeLatitude, ?string $directionLongitude = null, ?string $directionLatitude = null, string $format = GPSPosition::FORMAT_DMS_SHORT_1): array
    {
        return (new Coordinate(new GPSPosition(self::parseDecimalDegree($decimalDegreeLongitude, $directionLongitude)), new GPSPosition(self::parseDecimalDegree($decimalDegreeLatitude, $directionLatitude))))->getDms($format);
    }

    /**
     * Converts given decimal degree into coordinate.
     *
     * @throws Exception
     */
    public static function decimalDegree2Coordinate(float $decimalDegreeLatitude, float $decimalDegreeLongitude, ?string $directionLatitude = null, ?string $directionLongitude = null): Coordinate
    {
        if ($directionLatitude === null) {
            $directionLatitude = $decimalDegreeLatitude < 0 ? Coordinate::DIRECTION_SOUTH : Coordinate::DIRECTION_NORTH;
        }

        if ($directionLongitude === null) {
            $directionLongitude = $decimalDegreeLongitude < 0 ? Coordinate::DIRECTION_WEST : Coordinate::DIRECTION_EAST;
        }

        $decimalDegreeLatitude = abs($decimalDegreeLatitude);
        $decimalDegreeLongitude = abs($decimalDegreeLongitude);

        return new Coordinate(
            new GPSPosition(self::parseDecimalDegree($decimalDegreeLatitude, $directionLatitude)),
            new GPSPosition(self::parseDecimalDegree($decimalDegreeLongitude, $directionLongitude))
        );
    }

    /**
     * Converts given decimal degree into google link.
     *
     * @throws Exception
     */
    public static function decimalDegree2GoogleLink(float $decimalDegreeLatitude, float $decimalDegreeLongitude, ?string $directionLatitude = null, ?string $directionLongitude = null): string
    {
        $coordinate = self::decimalDegree2Coordinate($decimalDegreeLatitude, $decimalDegreeLongitude, $directionLatitude, $directionLongitude);

        return $coordinate->getGoogle();
    }

    /**
     * Converts given decimal degree into openstreetmap link.
     *
     * @throws Exception
     */
    public static function decimalDegree2OpenstreetmapLink(float $decimalDegreeLatitude, float $decimalDegreeLongitude, ?string $directionLatitude = null, ?string $directionLongitude = null): string
    {
        $coordinate = self::decimalDegree2Coordinate($decimalDegreeLatitude, $decimalDegreeLongitude, $directionLatitude, $directionLongitude);

        return $coordinate->getOpenstreetmap();
    }

    /**
     * self::getDegree helper. To split given string.
     *
     * @throws Exception
     */
    public static function getDegreeString(string $decimalDegreeFrom, string $decimalDegreeTo, int $decimals = 2): float
    {
        $decimalDegreeFromExploded = preg_split('~[, ]~', $decimalDegreeFrom);

        if ($decimalDegreeFromExploded === false) {
            throw new Exception(sprintf('Unable to split given string "%s" (%s:%d).', $decimalDegreeFrom, __FILE__, __LINE__));
        }

        [$decimalDegreeFromLongitude, $decimalDegreeFromLatitude] = $decimalDegreeFromExploded;

        $decimalDegreeToExploded = preg_split('~[, ]~', $decimalDegreeTo);

        if ($decimalDegreeToExploded === false) {
            throw new Exception(sprintf('Unable to split given string "%s" (%s:%d).', $decimalDegreeTo, __FILE__, __LINE__));
        }

        [$decimalDegreeToLongitude, $decimalDegreeToLatitude] = $decimalDegreeToExploded;

        return self::getDegree(
            floatval($decimalDegreeFromLongitude),
            floatval($decimalDegreeFromLatitude),
            floatval($decimalDegreeToLongitude),
            floatval($decimalDegreeToLatitude),
            $decimals
        );
    }

    /**
     * Calculates the degree from given source coordinate to target coordinate.
     * Attention! Expects the right sign! No direction within the parameters.
     * - Longitude W must be -Longitude
     * - Latitude S must be -Latitude
     * - Direction N: 0°
     * - Direction E: 90°
     * - Direction S: 180°
     * - Direction W: -90°
     *
     * @param float $decimalDegreeLatitudeFrom (yFrom)
     * @param float $decimalDegreeLongitudeFrom (xFrom)
     * @param float $decimalDegreeLatitudeTo (yTo)
     * @param float $decimalDegreeLongitudeTo (xTo)
     */
    public static function getDegree(float $decimalDegreeLatitudeFrom, float $decimalDegreeLongitudeFrom, float $decimalDegreeLatitudeTo, float $decimalDegreeLongitudeTo, int $decimals = 2): float
    {
        if ($decimalDegreeLatitudeFrom === $decimalDegreeLatitudeTo && $decimalDegreeLongitudeFrom === $decimalDegreeLongitudeTo) {
            return round(.0, $decimals);
        }

        $deltaX = $decimalDegreeLongitudeTo - $decimalDegreeLongitudeFrom;
        $deltaY = $decimalDegreeLatitudeTo - $decimalDegreeLatitudeFrom;

        $rad = atan2($deltaY, $deltaX);

        $degree = -1 * $rad * (self::ANGLE_180_0 / M_PI);

        $degree += self::ANGLE_90_0;

        $degree -= $degree > self::ANGLE_180_0 ? self::ANGLE_360_0 : self::ANGLE_0_0;

        return round($degree, $decimals);
    }

    /**
     * self::getDirectionFromDegreeString helper. To split given string.
     *
     * @throws Exception
     */
    public static function getDirectionFromPositionsString(string $decimalDegreeFrom, string $decimalDegreeTo): string
    {
        $degree = self::getDegreeString($decimalDegreeFrom, $decimalDegreeTo);

        return self::getDirectionFromDegree($degree);
    }

    /**
     * Calculates the direction from given source coordinate to target coordinate.
     * Attention! Expects the right sign! No direction within the parameters.
     * - Longitude W must be -Longitude
     * - Latitude S must be -Latitude
     *
     * @throws Exception
     */
    public static function getDirectionFromPositions(float $decimalDegreeLatitudeFrom, float $decimalDegreeLongitudeFrom, float $decimalDegreeLatitudeTo, float $decimalDegreeLongitudeTo): string
    {
        $degree = self::getDegree($decimalDegreeLatitudeFrom, $decimalDegreeLongitudeFrom, $decimalDegreeLatitudeTo, $decimalDegreeLongitudeTo);

        return self::getDirectionFromDegree($degree);
    }

    /**
     * Gets direction from degree.
     *
     * @throws Exception
     */
    public static function getDirectionFromDegree(float $degree): string
    {
        if ($degree > self::ANGLE_180_0) {
            throw new Exception(sprintf('Unexpected angle given 1 "%.2f" (%s:%d).', $degree, __FILE__, __LINE__));
        }

        if ($degree < -self::ANGLE_180_0) {
            throw new Exception(sprintf('Unexpected angle given 2 "%.2f" (%s:%d).', $degree, __FILE__, __LINE__));
        }

        return match (true) {
            $degree >= -self::ANGLE_22_5 && $degree < self::ANGLE_22_5 => 'N',
            $degree >= self::ANGLE_22_5 && $degree < self::ANGLE_67_5 => 'NE',
            $degree >= self::ANGLE_67_5 && $degree < self::ANGLE_112_5 => 'E',
            $degree >= self::ANGLE_112_5 && $degree < self::ANGLE_157_5 => 'SE',
            $degree >= self::ANGLE_157_5 || $degree < -self::ANGLE_157_5 => 'S',
            $degree >= -self::ANGLE_157_5 && $degree < -self::ANGLE_112_5 => 'SW',
            $degree >= -self::ANGLE_112_5 && $degree < -self::ANGLE_67_5 => 'W',
            $degree >= -self::ANGLE_67_5 && $degree < -self::ANGLE_22_5 => 'NW',
            default => throw new Exception(sprintf('Unexpected angle given 3 "%.2f" (%s:%d).', $degree, __FILE__, __LINE__)),
        };
    }

    /**
     * Parse full location string and converts it to a float array.
     *
     * Allowed formats:
     * ----------------
     * • '47.900635 13.601868'
     * • '47.900635, 13.601868'
     * • '47.900635,13.601868'
     * • '47.900635°,13.601868°'
     * • '47.900635° -13.601868°'
     * • '47.900635°, -13.601868°'
     * • '47.900635°,-13.601868°'
     * • '47.900635°,_13.601868°'
     * • '47°54′2.286″E 13°36′6.7248″N'
     * • 'E47°54′2.286″ N13°36′6.7248″'
     * • etc.
     *
     * @return float[]|false
     * @throws Exception
     */
    public static function parseFullLocation2DecimalDegrees(string $fullLocation): array|false
    {
        $fullLocation = trim($fullLocation);

        $matches = [];
        switch (true) {
            /* Google redirect link https://maps.app.goo.gl/PHq5axBaDdgRWj4T6 */
            case preg_match(self::REGEXP_GOOGLE_REDIRECT, $fullLocation, $matches):
                [$latitude, $longitude] = self::parseLatitudeAndLongitudeFromGoogleLink($matches[1]);
                break;
                /* Google spot link https://www.google.de/maps/place/Strandbad+Wannsee+-+Berliner+B%C3%A4der/@52.4286142,13.1557256,13.54z/data=!4m5!3m4!1s0x47a858ffef30e359:0x165816b49cc6929a!8m2!3d52.4381357!4d13.1794242 */
            case preg_match(self::REGEXP_ENCRYPTED_LATITUDE_LONGITUDE, $fullLocation, $matches):
                $parsed = self::parseLatitudeAndLongitudeFromGoogleLinkDirect($fullLocation);

                if ($parsed === false) {
                    throw new Exception(sprintf('Unable to parse google link "%s" (%s:%d).', $fullLocation, __FILE__, __LINE__));
                }

                [$latitude, $longitude] = $parsed;
                break;
                /* Given location */
            case preg_match(
                sprintf(
                    '~((?:%s)|(?:%s))[, ]+((?:%s)|(?:%s))~',
                    self::REGEXP_DECIMAL,
                    self::REGEXP_DMS,
                    self::REGEXP_DECIMAL,
                    self::REGEXP_DMS
                ),
                $fullLocation,
                $matches
            ):
                $fullLocation = sprintf('%s,%s', $matches[1], $matches[2]);

                if (
                    preg_match(
                        sprintf(
                            '~%s,%s~',
                            self::REGEXP_DMS_2,
                            self::REGEXP_DMS_2
                        ),
                        $fullLocation,
                        $matches
                    )
                ) {
                    $fullLocation = sprintf('%s%s%s%s%s,%s%s%s%s%s', $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6], $matches[7], $matches[8], $matches[9], $matches[10]);

                    /* Replace german O to english E */
                    $fullLocation = str_replace('O', 'E', $fullLocation);
                }

                [$latitude, $longitude] = self::parseLatitudeAndLongitudeFromString($fullLocation);
                break;
            default:
                return false;
        }

        return [$latitude, $longitude];
    }

    /**
     * Parses Google Link (name; direct; without redirect).
     *
     * @throws Exception
     */
    public static function parseNameFromGoogleLinkDirect(string $googleLink): string|false
    {
        $matchesLocation = [];
        if (!preg_match('~/maps/place/([^/]+)/~', $googleLink, $matchesLocation)) {
            return false;
        }

        $name = urldecode($matchesLocation[1]);

        return str_replace('+', ' ', $name);
    }

    /**
     * Parses Google Link (latitude; longitude; direct; without redirect).
     *
     * @return float[]|false
     * @throws Exception
     */
    public static function parseLatitudeAndLongitudeFromGoogleLinkDirect(string $googleLink): array|false
    {
        $matchesLocation = [];
        if (!preg_match(self::REGEXP_ENCRYPTED_LATITUDE_LONGITUDE, $googleLink, $matchesLocation)) {
            return false;
        }
        [, $latitude, $longitude] = $matchesLocation;

        $latitude = floatval($latitude);
        $longitude = floatval($longitude);

        return [$latitude, $longitude];
    }

    /**
     * @param array<int, string> $units
     * @throws Exception
     */
    protected static function getRegexp(array $units, int $version): string
    {
        return match ($version) {
            self::REGEXP_VERSION_1 => sprintf(
                '~^([0-9]+)(?:%s)[ ]*([0-9]+)(?:%s)[ ]*([0-9]+(?:\.[0-9]+)?)(?:%s)[ ]*(%s)$~',
                implode('|', self::UNIT_DEGREE),
                implode('|', self::UNIT_MINUTES),
                implode('|', self::UNIT_SECONDS),
                implode('|', $units)
            ),
            self::REGEXP_VERSION_2 => sprintf(
                '~^(%s)[ ]*([0-9]+)(?:%s)[ ]*([0-9]+)(?:%s)[ ]*([0-9]+(?:\.[0-9]+)?)(?:%s)$~',
                implode('|', $units),
                implode('|', self::UNIT_DEGREE),
                implode('|', self::UNIT_MINUTES),
                implode('|', self::UNIT_SECONDS)
            ),
            default => throw new Exception(sprintf('Unknown version "%d" given (%s:%d).', $version, __FILE__, __LINE__))
        };
    }

    /**
     * Returns array from given values.
     *
     * @return array<string, int|float|string>
     * @throws Exception
     */
    #[ArrayShape([
        'degree' => 'int',
        'minutes' => 'int',
        'seconds' => 'float',
        'direction' => 'string',
        'type' => 'string',
    ])]
    protected static function getData(int $degree, int $minutes, float $seconds, ?string $direction = null): array
    {
        $data = [
            'degree' => $degree,
            'minutes' => $minutes,
            'seconds' => $seconds,
        ];

        if ($direction !== null) {
            $data = array_merge(
                $data,
                [
                    'type' => self::getType($direction),
                    'direction' => $direction,
                ]
            );
        }

        return $data;
    }

    /**
     * Parses Google Link.
     *
     * @return float[]
     * @throws Exception
     */
    protected static function parseLatitudeAndLongitudeFromGoogleLink(string $googleLink): array
    {
        $curl = curl_init($googleLink);

        if ($curl === false) {
            throw new Exception(sprintf('Unable to initiate curl (%s:%d).', __FILE__, __LINE__));
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $response = curl_exec($curl);

        if (is_bool($response)) {
            throw new Exception(sprintf('Unable to exec curl (%s:%d).', __FILE__, __LINE__));
        }

        $header_size = intval(curl_getinfo($curl, CURLINFO_HEADER_SIZE));

        $headerLines = substr($response, 0, $header_size);

        // location: https://www.google.com/maps/place/Malbork,+Polen/data=!4m6!3m5!1s0x46fd5bffa9b675d5:0xb4e2fe366cccb936!7e2!8m2!3d54.073048299999996!4d18.992402?utm_source=mstt_1&entry=gps
        $matchesLocation = [];
        if (!preg_match(self::REGEXP_GOOGLE_LOCATION_REDIRECT, $headerLines, $matchesLocation)) {
            throw new InvalidArgumentException(sprintf('Unable to parse header from google link "%s".', ${$googleLink}));
        }

        [, $latitude, $longitude] = $matchesLocation;

        return [floatval($latitude), floatval($longitude)];
    }

    /**
     * Parse from full location string.
     *
     * @return float[]
     * @throws Exception
     */
    protected static function parseLatitudeAndLongitudeFromString(string $fullLocation): array
    {
        $split = preg_split(self::REGEXP_SPLIT_LATITUDE_LONGITUDE, $fullLocation);

        if ($split === false) {
            throw new Exception(sprintf('Unable to split given full location string (%s:%d).', __FILE__, __LINE__));
        }

        [$latitude, $longitude] = $split;

        $latitude = trim(str_replace('_', '-', $latitude));
        $longitude = trim(str_replace('_', '-', $longitude));

        $numberRegexp = '~^-?\d+\.\d+[°]*~';

        if (preg_match($numberRegexp, $latitude)) {
            $latitude = floatval($latitude);
        } else {
            $latitude = self::dms2DecimalDegree($latitude);
        }

        if (preg_match($numberRegexp, $longitude)) {
            $longitude = floatval($longitude);
        } else {
            $longitude = self::dms2DecimalDegree($longitude);
        }

        return [$latitude, $longitude];
    }
}
