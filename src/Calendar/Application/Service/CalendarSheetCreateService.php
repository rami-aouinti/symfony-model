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

namespace App\Calendar\Application\Service;

use App\Calendar\Application\Service\Entity\CalendarLoaderService;
use App\Calendar\Application\Service\Entity\HolidayGroupLoaderService;
use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\Calendar\Domain\Entity\HolidayGroup;
use App\User\Application\Service\SecurityService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-03-13)
 * @package App\Service
 */
class CalendarSheetCreateService
{
    public function __construct(
        protected CalendarLoaderService $calendarLoaderService,
        protected HolidayGroupLoaderService $holidayGroupLoaderService,
        protected SecurityService $securityService,
        protected KernelInterface $appKernel
    ) {
    }

    /**
     * Creates the calendar sheet.
     *
     * @return float[]|array<string|int>[]
     * @throws NonUniqueResultException
     * @throws Exception
     */
    #[ArrayShape([
        'file' => 'mixed',
        'time' => 'float',
    ])]
    public function create(CalendarImage $calendarImage, HolidayGroup $holidayGroup, int $qrCodeVersion = 5, bool $deleteTargetImages = false): array
    {
        /** @var ?Calendar $calendar */
        $calendar = $calendarImage->getCalendar();

        if ($calendar === null) {
            throw new Exception(sprintf('Calendar class not found (%s:%d).', __FILE__, __LINE__));
        }

        /* Read parameters */
        $userId = $this->securityService->getUser()->getId();

        if ($userId === null) {
            throw new Exception(sprintf('Unable to find user id (%s:%d).', __FILE__, __LINE__));
        }

        /* Create calendar image */
        $timeStart = microtime(true);
        $calendarBuilderService = new CalendarBuilderService($this->appKernel);
        $calendarBuilderService->init($calendarImage, $holidayGroup, false, true, CalendarImage::QUALITY_TARGET, $qrCodeVersion, $deleteTargetImages);
        $file = $calendarBuilderService->build();
        $timeTaken = microtime(true) - $timeStart;

        return [
            'file' => $file,
            'time' => $timeTaken,
        ];
    }
}
