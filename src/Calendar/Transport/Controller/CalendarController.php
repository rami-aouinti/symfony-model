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

namespace App\Calendar\Transport\Controller;

use App\Calendar\Application\Service\Entity\CalendarLoaderService;
use App\Calendar\Application\Service\UrlService;
use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Infrastructure\Repository\CalendarImageRepository;
use App\Platform\Transport\Controller\Base\BaseController;
use App\User\Application\Service\Entity\UserLoaderService;
use App\User\Application\Service\SecurityService;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-03-18)
 * @package App\Controller
 */
class CalendarController extends BaseController
{
    public function __construct(
        protected UserLoaderService $userLoaderService,
        protected CalendarLoaderService $calendarLoaderService,
        protected CalendarImageRepository $calendarImageRepository,
        protected SecurityService $securityService
    ) {
    }

    /**
     * Index route.
     *
     * @throws Exception
     */
    #[Route('/calendar/{hash}/{userId}/{calendarId}', name: BaseController::ROUTE_NAME_APP_CALENDAR_INDEX)]
    public function index(string $hash, string $userId, int $calendarId): Response
    {
        $this->userLoaderService->loadUserCheckHash($userId, $hash);

        $calendar = $this->calendarLoaderService->loadCalendar($userId, $calendarId);

        if ($this->allowedToSeeCalendar($calendar)) {
            return $this->render('calendar/index.html.twig', [
                'calendar' => $calendar,
            ]);
        }

        throw $this->createNotFoundException();
    }

    /**
     * Encoded index route (standard).
     *
     * @throws Exception
     */
    #[Route('/calendar/{encoded}', name: BaseController::ROUTE_NAME_APP_CALENDAR_INDEX_ENCODED)]
    public function indexEncoded(string $encoded): Response
    {
        $parameters = UrlService::decode(BaseController::CONFIG_APP_CALENDAR_INDEX, $encoded);

        $hash = strval($parameters['hash']);
        $userId = strval($parameters['userId']);
        $calendarId = intval($parameters['calendarId']);

        return $this->index($hash, $userId, $calendarId);
    }

    /**
     * Encoded index route (short).
     *
     * @throws Exception
     */
    #[Route('/c/{encoded}', name: BaseController::ROUTE_NAME_APP_CALENDAR_INDEX_ENCODED_SHORT)]
    public function indexEncodedShort(string $encoded): Response
    {
        $parameters = UrlService::decode(BaseController::CONFIG_APP_CALENDAR_INDEX, $encoded);

        $hash = strval($parameters['hash']);
        $userId = strval($parameters['userId']);
        $calendarId = intval($parameters['calendarId']);

        /* Loads user and gets the full hash. */
        $user = $this->userLoaderService->loadUserCheckHash($userId, $hash, true);

        /* Get encoded string. */
        $encoded = UrlService::encode(BaseController::CONFIG_APP_CALENDAR_INDEX, [
            'hash' => $user->getIdHash(),
            'userId' => $userId,
            'calendarId' => $calendarId,
        ]);

        /* Redirect to standard URL. */
        return $this->redirectToRoute(BaseController::ROUTE_NAME_APP_CALENDAR_INDEX_ENCODED, [
            'encoded' => $encoded,
        ], Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * Detail route.
     *
     * @throws Exception
     */
    #[Route('/calendar/detail/{hash}/{userId}/{calendarImageId}', name: BaseController::ROUTE_NAME_APP_CALENDAR_DETAIL)]
    public function detail(string $hash, string $userId, int $calendarImageId): Response
    {
        $this->userLoaderService->loadUserCheckHash($userId, $hash);

        $calendarImage = $this->calendarLoaderService->loadCalendarImageByUserHashAndCalendarImage($hash, $userId, $calendarImageId);

        $calendar = $calendarImage->getCalendar();

        if (!$calendar instanceof Calendar) {
            throw new Exception(sprintf('Unable to get calendar (%s:%d).', __FILE__, __LINE__));
        }

        if ($this->allowedToSeeCalendar($calendar)) {
            return $this->render('calendar/detail.html.twig', [
                'calendarImage' => $calendarImage,
            ]);
        }

        throw $this->createNotFoundException();
    }

    /**
     * Encoded detail route (standard).
     *
     * @throws Exception
     */
    #[Route('/calendar/detail/{encoded}', name: BaseController::ROUTE_NAME_APP_CALENDAR_DETAIL_ENCODED)]
    public function detailEncoded(string $encoded): Response
    {
        $parameters = UrlService::decode(BaseController::CONFIG_APP_CALENDAR_DETAIL, $encoded);

        $hash = strval($parameters['hash']);
        $userId = strval($parameters['userId']);
        $calendarImageId = intval($parameters['calendarImageId']);

        return $this->detail($hash, $userId, $calendarImageId);
    }

    /**
     * Encoded detail route (short).
     *
     * @throws Exception
     */
    #[Route('/d/{encoded}', name: BaseController::ROUTE_NAME_APP_CALENDAR_DETAIL_ENCODED_SHORT)]
    public function detailEncodedShort(string $encoded): Response
    {
        $parameters = UrlService::decode(BaseController::CONFIG_APP_CALENDAR_DETAIL, $encoded);

        $hash = strval($parameters['hash']);
        $userId = strval($parameters['userId']);
        $calendarImageId = intval($parameters['calendarImageId']);

        /* Loads user and gets the full hash. */
        $user = $this->userLoaderService->loadUserCheckHash($userId, $hash, true);

        /* Get encoded string. */
        $encoded = UrlService::encode(BaseController::CONFIG_APP_CALENDAR_DETAIL, [
            'hash' => $user->getIdHash(),
            'userId' => $userId,
            'calendarImageId' => $calendarImageId,
        ]);

        /* Redirect to standard URL. */
        return $this->redirectToRoute(BaseController::ROUTE_NAME_APP_CALENDAR_DETAIL_ENCODED, [
            'encoded' => $encoded,
        ], Response::HTTP_TEMPORARY_REDIRECT);
    }

    /**
     * This method checks, whether the user is allowed to see that given calendar.
     *
     * @throws Exception
     */
    protected function allowedToSeeCalendar(Calendar $calendar): bool
    {
        $published = $calendar->getPublished();

        $own = $this->securityService->isUserLoggedIn() && $this->securityService->getUser() === $calendar->getUser();

        $admin = $this->securityService->isGrantedByAnAdmin();

        return $published || $own || $admin;
    }
}
