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

namespace App\Tests\Unit\Service;

use App\Media\Application\Service\ImageDataService;
use App\Platform\Application\Utils\StringConverter;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-04-25)
 * @package App\Tests\Unit\Utils
 */
final class ImageDataServiceTest extends TestCase
{
    /**
     * Test wrapper.
     *
     * @dataProvider dataProvider
     *
     * @testdox $number) Test SizeConverter: $method
     * @param array<int, array<int, array<string, array<string, float|int|string|null>>|int|string>> $expected
     * @throws Exception
     */
    public function testWrapper(int $number, string $imagePath, array $expected): void
    {
        /* Arrange */
        $imageData = new ImageDataService($imagePath);

        /* Act */
        $current = $imageData->getImageDataFull();

        /* Assert */
        $this->assertEquals($expected, $current);
    }

    /**
     * Data provider.
     *
     * @return array<int, array<int, array<string, array<string, DateTime|float|int|string|null>>|int|string>>
     */
    public function dataProvider(): array
    {
        $number = 0;

        return [
            /**
             * Image, With GPS
             */
            [++$number, 'data/tests/images/properties/img-with-gps.jpg', [
                'device-manufacturer' => [
                    'title' => 'Device Manufacturer',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'OnePlus',
                    'value-formatted' => 'OnePlus',
                ],
                'device-model' => [
                    'title' => 'Device Model',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'IN2023',
                    'value-formatted' => 'IN2023',
                ],
                'exif-version' => [
                    'title' => 'Exif Version',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '0220',
                    'value-formatted' => '0220',
                ],
                'gps-google-link' => [
                    'title' => 'GPS Google',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'https://www.google.de/maps/place/50.062819+14.417200',
                    'value-formatted' => 'https://www.google.de/maps/place/50.062819+14.417200',
                ],
                'gps-height' => [
                    'title' => 'GPS Height',
                    'format' => '%.2f',
                    'unit' => ' m',
                    'unit-before' => null,
                    'value' => 278.471,
                    'value-formatted' => '278.471 m',
                ],
                'gps-latitude-decimal-degree' => [
                    'title' => 'GPS Latitude Decimal Degree',
                    'format' => '%s',
                    'unit' => '°',
                    'unit-before' => null,
                    'value' => 50.062819,
                    'value-formatted' => '50.062819°',
                ],
                'gps-latitude-direction' => [
                    'title' => 'GPS Latitude Direction',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'N',
                    'value-formatted' => 'N',
                ],
                'gps-latitude-dms' => [
                    'title' => 'GPS Latitude DMS',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '50°3’46.15"N',
                    'value-formatted' => '50°3’46.15"N',
                ],
                'gps-longitude-decimal-degree' => [
                    'title' => 'GPS Longitude Decimal Degree',
                    'format' => '%s',
                    'unit' => '°',
                    'unit-before' => null,
                    'value' => 14.4172,
                    'value-formatted' => '14.4172°',
                ],
                'gps-longitude-direction' => [
                    'title' => 'GPS Longitude Direction',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'E',
                    'value-formatted' => 'E',
                ],
                'gps-longitude-dms' => [
                    'title' => 'GPS Longitude',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '14°25’1.92"E',
                    'value-formatted' => '14°25’1.92"E',
                ],
                'image-aperture' => [
                    'title' => 'Image Aperture',
                    'format' => '%.1f',
                    'unit' => null,
                    'unit-before' => 'F/',
                    'value' => 2.2,
                    'value-formatted' => 'F/2.2',
                ],
                'image-date-time-original' => [
                    'title' => 'Image Date Time Original',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '2021-11-21T14:12:29',
                    'value-formatted' => '2021-11-21T14:12:29',
                    'value-date-time' => StringConverter::convertDateTime('2021-11-21T14:12:29'),
                ],
                'image-exposure-bias-value' => [
                    'title' => 'Image Exposure Bias Value',
                    'format' => '%d',
                    'unit' => ' steps',
                    'unit-before' => null,
                    'value' => 0,
                    'value-formatted' => '0 steps',
                ],
                'image-exposure-time' => [
                    'title' => 'Image Exposure Time',
                    'format' => '%s',
                    'unit' => ' s',
                    'unit-before' => null,
                    'value' => 0.01266,
                    'value-formatted' => '1/79 s',
                    'value-original' => '1/79',
                ],
                'image-filename' => [
                    'title' => 'Image Filename',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'img-with-gps.jpg',
                    'value-formatted' => 'img-with-gps.jpg',
                ],
                'image-focal-length' => [
                    'title' => 'Image Focal Length',
                    'format' => '%d',
                    'unit' => ' mm',
                    'unit-before' => null,
                    'value' => 3.05,
                    'value-formatted' => '3.05 mm',
                ],
                'image-height' => [
                    'title' => 'Image Height',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 480,
                    'value-formatted' => '480 px',
                ],
                'image-iso' => [
                    'title' => 'Image ISO',
                    'format' => '%d',
                    'unit' => null,
                    'unit-before' => 'ISO-',
                    'value' => 100,
                    'value-formatted' => 'ISO-100',
                ],
                'image-mime' => [
                    'title' => 'Image Mime',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'image/jpeg',
                    'value-formatted' => 'image/jpeg',
                ],
                'image-size' => [
                    'title' => 'Image Size',
                    'format' => '%d',
                    'unit' => ' Bytes',
                    'unit-before' => null,
                    'value' => 105514,
                    'value-formatted' => '105514 Bytes',
                ],
                'image-size-human' => [
                    'title' => 'Image Size Human',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '103.04 kB',
                    'value-formatted' => '103.04 kB',
                ],
                'image-width' => [
                    'title' => 'Image Width',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 640,
                    'value-formatted' => '640 px',
                ],
                'image-x-resolution' => [
                    'title' => 'Image X-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 72,
                    'value-formatted' => '72 dpi',
                ],
                'image-y-resolution' => [
                    'title' => 'Image Y-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 72,
                    'value-formatted' => '72 dpi',
                ],
            ]],

            /**
             * Image, Without GPS
             */
            [++$number, 'data/tests/images/properties/img-without-gps.jpg', [
                'device-manufacturer' => [
                    'title' => 'Device Manufacturer',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'SONY',
                    'value-formatted' => 'SONY',
                ],
                'device-model' => [
                    'title' => 'Device Model',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'ILCE-7M2',
                    'value-formatted' => 'ILCE-7M2',
                ],
                'exif-version' => [
                    'title' => 'Exif Version',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '0231',
                    'value-formatted' => '0231',
                ],
                'image-aperture' => [
                    'title' => 'Image Aperture',
                    'format' => '%.1f',
                    'unit' => null,
                    'unit-before' => 'F/',
                    'value' => 4,
                    'value-formatted' => 'F/4',
                ],
                'image-date-time-original' => [
                    'title' => 'Image Date Time Original',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '2021-06-24T12:48:44',
                    'value-formatted' => '2021-06-24T12:48:44',
                    'value-date-time' => StringConverter::convertDateTime('2021-06-24T12:48:44'),
                ],
                'image-exposure-bias-value' => [
                    'title' => 'Image Exposure Bias Value',
                    'format' => '%d',
                    'unit' => ' steps',
                    'unit-before' => null,
                    'value' => -1,
                    'value-formatted' => '-1 steps',
                ],
                'image-exposure-time' => [
                    'title' => 'Image Exposure Time',
                    'format' => '%s',
                    'unit' => ' s',
                    'unit-before' => null,
                    'value' => 0.00125,
                    'value-formatted' => '1/800 s',
                    'value-original' => '1/800',
                ],
                'image-filename' => [
                    'title' => 'Image Filename',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'img-without-gps.jpg',
                    'value-formatted' => 'img-without-gps.jpg',
                ],
                'image-focal-length' => [
                    'title' => 'Image Focal Length',
                    'format' => '%d',
                    'unit' => ' mm',
                    'unit-before' => null,
                    'value' => 50,
                    'value-formatted' => '50 mm',
                ],
                'image-height' => [
                    'title' => 'Image Height',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 480,
                    'value-formatted' => '480 px',
                ],
                'image-iso' => [
                    'title' => 'Image ISO',
                    'format' => '%d',
                    'unit' => null,
                    'unit-before' => 'ISO-',
                    'value' => 50,
                    'value-formatted' => 'ISO-50',
                ],
                'image-mime' => [
                    'title' => 'Image Mime',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'image/jpeg',
                    'value-formatted' => 'image/jpeg',
                ],
                'image-size' => [
                    'title' => 'Image Size',
                    'format' => '%d',
                    'unit' => ' Bytes',
                    'unit-before' => null,
                    'value' => 200642,
                    'value-formatted' => '200642 Bytes',
                ],
                'image-size-human' => [
                    'title' => 'Image Size Human',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '195.94 kB',
                    'value-formatted' => '195.94 kB',
                ],
                'image-width' => [
                    'title' => 'Image Width',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 720,
                    'value-formatted' => '720 px',
                ],
                'image-x-resolution' => [
                    'title' => 'Image X-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 240,
                    'value-formatted' => '240 dpi',
                ],
                'image-y-resolution' => [
                    'title' => 'Image Y-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 240,
                    'value-formatted' => '240 dpi',
                ],
            ]],

            /**
             * Image, Without GPS, Without focal
             */
            [++$number, 'data/tests/images/properties/img-without-focal.jpg', [
                'device-manufacturer' => [
                    'title' => 'Device Manufacturer',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'SONY',
                    'value-formatted' => 'SONY',
                ],
                'device-model' => [
                    'title' => 'Device Model',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'ILCE-7M2',
                    'value-formatted' => 'ILCE-7M2',
                ],
                'exif-version' => [
                    'title' => 'Exif Version',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '0231',
                    'value-formatted' => '0231',
                ],
                'image-date-time-original' => [
                    'title' => 'Image Date Time Original',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '2021-11-07T18:56:01',
                    'value-formatted' => '2021-11-07T18:56:01',
                    'value-date-time' => StringConverter::convertDateTime('2021-11-07T18:56:01'),
                ],
                'image-exposure-bias-value' => [
                    'title' => 'Image Exposure Bias Value',
                    'format' => '%d',
                    'unit' => ' steps',
                    'unit-before' => null,
                    'value' => -0.7,
                    'value-formatted' => '-0.7 steps',
                ],
                'image-exposure-time' => [
                    'title' => 'Image Exposure Time',
                    'format' => '%s',
                    'unit' => ' s',
                    'unit-before' => null,
                    'value' => 0.005,
                    'value-formatted' => '1/200 s',
                    'value-original' => '1/200',
                ],
                'image-filename' => [
                    'title' => 'Image Filename',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'img-without-focal.jpg',
                    'value-formatted' => 'img-without-focal.jpg',
                ],
                'image-height' => [
                    'title' => 'Image Height',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 480,
                    'value-formatted' => '480 px',
                ],
                'image-iso' => [
                    'title' => 'Image ISO',
                    'format' => '%d',
                    'unit' => null,
                    'unit-before' => 'ISO-',
                    'value' => 50,
                    'value-formatted' => 'ISO-50',
                ],
                'image-mime' => [
                    'title' => 'Image Mime',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'image/jpeg',
                    'value-formatted' => 'image/jpeg',
                ],
                'image-size' => [
                    'title' => 'Image Size',
                    'format' => '%d',
                    'unit' => ' Bytes',
                    'unit-before' => null,
                    'value' => 184780,
                    'value-formatted' => '184780 Bytes',
                ],
                'image-size-human' => [
                    'title' => 'Image Size Human',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '180.45 kB',
                    'value-formatted' => '180.45 kB',
                ],
                'image-width' => [
                    'title' => 'Image Width',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 702,
                    'value-formatted' => '702 px',
                ],
                'image-x-resolution' => [
                    'title' => 'Image X-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 240,
                    'value-formatted' => '240 dpi',
                ],
                'image-y-resolution' => [
                    'title' => 'Image Y-Resolution',
                    'format' => '%d',
                    'unit' => ' dpi',
                    'unit-before' => null,
                    'value' => 240,
                    'value-formatted' => '240 dpi',
                ],
            ]],

            /**
             * Image, Without EXIF
             */
            [++$number, 'data/tests/images/properties/img-without-exif.jpg', [
                'image-height' => [
                    'title' => 'Image Height',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 480,
                    'value-formatted' => '480 px',
                ],
                'image-mime' => [
                    'title' => 'Image Mime',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => 'image/jpeg',
                    'value-formatted' => 'image/jpeg',
                ],
                'image-size' => [
                    'title' => 'Image Size',
                    'format' => '%d',
                    'unit' => ' Bytes',
                    'unit-before' => null,
                    'value' => 113490,
                    'value-formatted' => '113490 Bytes',
                ],
                'image-size-human' => [
                    'title' => 'Image Size Human',
                    'format' => '%s',
                    'unit' => null,
                    'unit-before' => null,
                    'value' => '110.83 kB',
                    'value-formatted' => '110.83 kB',
                ],
                'image-width' => [
                    'title' => 'Image Width',
                    'format' => '%d',
                    'unit' => ' px',
                    'unit-before' => null,
                    'value' => 679,
                    'value-formatted' => '679 px',
                ],
            ]],
        ];
    }
}
