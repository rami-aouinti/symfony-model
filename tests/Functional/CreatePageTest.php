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

namespace App\Tests\Functional;

use App\Entity\CalendarImage;
use App\Entity\HolidayGroup;
use App\Property\Infrastructure\DataFixtures\AppFixtures;
use App\Service\CalendarBuilderService;
use App\Service\Entity\CalendarLoaderService;
use App\Service\Entity\HolidayGroupLoaderService;
use App\Tests\Library\DbHelper;
use App\Utils\ImageProperty;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Imagick;
use ImagickException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-01-08)
 * @package App\Tests\Unit\Utils
 * @see https://symfony.com/doc/current/testing.html
 */
final class CreatePageTest extends KernelTestCase
{
    protected static string $projectDir;

    protected static bool $keepDataBetweenTests = false;

    protected static bool $setUpDone = false;

    protected static bool $clearDB = true;
    private CalendarBuilderService $calendarBuilderService;

    private CalendarLoaderService $calendarLoaderService;

    private HolidayGroupLoaderService $holidayGroupLoaderService;

    /**
     * This method is called before class.
     *
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        /* If setup is already done. Stop here. */
        if (self::$setUpDone) {
            return;
        }

        /* Boot kernel to get AppKernel */
        self::bootKernel();

        /* Setup is already done */
        self::$setUpDone = true;

        /* Get project dir */
        self::$projectDir = self::$kernel->getProjectDir();

        if (self::$keepDataBetweenTests || !self::$clearDB) {
            return;
        }

        /* Build the db helper */
        $dbHelper = new DbHelper(self::$kernel);

        /* Empty test table */
        $dbHelper->printAndExecuteCommands([
            '/* Drop schema */' => 'doctrine:schema:drop --force --env=%(environment)s',
            '/* Create schema */' => 'doctrine:schema:create --env=%(environment)s',
            '/* Load fixtures */' => 'doctrine:fixtures:load -n --env=%(environment)s', # --group=test',
        ]);
    }

    /**
     * This method is called before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->calendarLoaderService = $this->getService(CalendarLoaderService::class);

        $this->holidayGroupLoaderService = $this->getService(HolidayGroupLoaderService::class);

        $this->calendarBuilderService = $this->getService(CalendarBuilderService::class);
    }

    /**
     * Create Page
     *
     * @throws Exception
     */
    public function testMain(): void
    {
        /* Parameter */
        $email = AppFixtures::getEmail(1);
        $calendarName = 'Calendar 1';
        $year = 2022;
        $month = 1;
        $holidayGroupName = AppFixtures::NAME_HOLIDAY_GROUP_SAXONY;

        /* Arrange */
        $calendarImage = $this->getCalendarImage($email, $calendarName, $year, $month);
        $holidayGroup = $this->getHolidayGroup($holidayGroupName);
        $calendar = $calendarImage->getCalendar();
        if ($calendar === null) {
            throw new Exception(sprintf('Calendar class is missing (%s:%d).', __FILE__, __LINE__));
        }
        $height = $calendar->getConfigObject()->getInt('height');
        $width = $calendar->getConfigObject()->getInt('width');

        /* Act */
        $this->calendarBuilderService->init($calendarImage, $holidayGroup, true);
        $file = $this->calendarBuilderService->build();
        $widthBuild = intval($file['widthTarget']);
        $heightBuild = intval($file['heightTarget']);
        $differenceValue = $this->compareImages($calendarImage, $widthBuild, $heightBuild);

        /* Assert */
        $this->assertSame($widthBuild, $width);
        $this->assertSame($heightBuild, $height);
        $this->assertLessThan(0.02, $differenceValue, sprintf('The difference is more than 0.02%% (%.2f%%).', $differenceValue));
    }

    /**
     * Load service from dependency injection.
     *
     * @template T of object
     * @param class-string<T> $className
     * @return T
     * @throws Exception
     */
    protected function getService(string $className, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): object
    {
        $service = self::$kernel->getContainer()->get($className, $invalidBehavior);

        /* Check service */
        if ($service === null) {
            throw new Exception(sprintf('Unable to get doctrine (%s:%d)', __FILE__, __LINE__));
        }

        if ($service instanceof $className) {
            return $service;
        }

        throw new Exception(sprintf('Wrong class (%s:%d)', __FILE__, __LINE__));
    }

    /**
     * Gets doctrine registry.
     *
     * @throws Exception
     */
    protected function getDoctrine(): Registry
    {
        /** @var ?Registry $doctrine */
        $doctrine = self::$kernel->getContainer()->get('doctrine');

        /* Check registry */
        if ($doctrine === null) {
            throw new Exception(sprintf('Unable to get doctrine (%s:%d)', __FILE__, __LINE__));
        }

        return $doctrine;
    }

    /**
     * Get HolidayGroup and CalendarImage resource from year and month.
     *
     * Todo: load fixtures into an in-memory database
     * - https://stackoverflow.com/questions/19368274/how-to-create-tests-w-doctrine-entities-without-persisting-them-how-to-set-id/41099633
     * - https://arueckauer.github.io/posts/2020/06/how-to-write-unit-tests-with-phpunit-involving-doctrine-entities/
     *
     * @throws NonUniqueResultException
     */
    protected function getCalendarImage(string $email, string $calendarName, int $year, int $month): CalendarImage
    {
        return $this->calendarLoaderService->loadCalendarImageByCalendarNameYearAndMonth($email, $calendarName, $year, $month);
    }

    /**
     * Get HolidayGroup from holiday group name.
     *
     * @throws Exception
     */
    protected function getHolidayGroup(string $holidayGroupName): HolidayGroup
    {
        return $this->holidayGroupLoaderService->loadHolidayGroup($holidayGroupName);
    }

    /**
     * Compares the images.
     *
     * @throws ImagickException
     * @throws Exception
     */
    protected function compareImages(CalendarImage $calendarImage, int $width, int $height): float
    {
        /* Get ImageProperty service. */
        $imageProperty = $this->getService(ImageProperty::class);

        /* Init the image objects. */
        $imageTarget = new Imagick();
        $imageExpected = new Imagick();

        /* Get image paths. */
        $pathTarget = $imageProperty->getPathImageTarget(calendarImage: $calendarImage, test: true);
        $pathExpected = $imageProperty->getPathImageExpected(calendarImage: $calendarImage, test: true);

        /* Set the fuzz factor (must be done BEFORE reading in the images). */
        $imageTarget->SetOption('fuzz', '2%');

        /* Read in the images. */
        $imageTarget->readImage($pathTarget);
        $imageExpected->readImage($pathExpected);

        /* Compare the images using METRIC=1 (Absolute Error). */
        $result = $imageExpected->compareImages($imageTarget, Imagick::METRIC_ABSOLUTEERRORMETRIC);

        /* Return the image comparison. */
        return floatval($result[1] / $width / $height * 100);
    }
}
