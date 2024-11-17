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

namespace App\Calendar\Application\Service\Entity;

use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\Calendar\Infrastructure\Repository\CalendarImageRepository;
use App\Calendar\Infrastructure\Repository\CalendarRepository;
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Service\Entity\Base\BaseLoaderService;
use App\User\Application\Service\Entity\UserLoaderService;
use App\User\Application\Service\SecurityService;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2021-12-31)
 * @package App\Command
 */
class CalendarLoaderService extends BaseLoaderService
{
    protected CalendarLoaderService $calendarLoaderService;

    protected User $user;

    protected Calendar $calendar;

    protected CalendarImage $calendarImage;

    protected Image $image;

    public function __construct(
        protected KernelInterface $appKernel,
        protected EntityManagerInterface $manager,
        protected SecurityService $securityService,
        protected UserLoaderService $userLoaderService
    ) {
    }

    /**
     * Loads all calendars.
     *
     * @return Calendar[]
     * @throws Exception
     */
    public function loadCalendars(): array
    {
        if ($this->securityService->isGrantedByAnAdmin()) {
            return $this->getCalendarRepository()->findAll();
        }

        return $this->getCalendarRepository()->findBy([
            'user' => $this->securityService->getUser(),
        ]);
    }

    /**
     * Loads and returns user
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function loadUser(string|int $userEmailOrId, bool $clearObjects = true): User
    {
        /* Clears all objects */
        if ($clearObjects) {
            $this->clear();
        }

        /* Load user */
        $user = match (true) {
            is_int($userEmailOrId) => $this->getUserRepository()->find($userEmailOrId),
            is_string($userEmailOrId) => $this->getUserRepository()->findOneByEmail($userEmailOrId),
        };
        if ($user === null) {
            throw new Exception(sprintf('Unable to find user with email "%s".', $userEmailOrId));
        }
        $this->user = $user;

        return $this->getUser();
    }

    /**
     * Loads and returns the calendar by given email and calendar name
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function loadCalendar(string|int $userEmailOrId, string|int $calendarNameOrId, bool $clearObjects = true): Calendar
    {
        /* Clears all objects */
        if ($clearObjects) {
            $this->clear();
        }

        /* Load User */
        $this->loadUser($userEmailOrId, false);

        /* Load calendar */
        $calendar = match (true) {
            is_int($calendarNameOrId) => $this->getCalendarRepository()->findOneByUserAndId($this->getUser(), $calendarNameOrId),
            is_string($calendarNameOrId) => $this->getCalendarRepository()->findOneByUserAndName($this->getUser(), $calendarNameOrId),
        };

        if ($calendar === null) {
            throw new Exception(sprintf('Unable to find calendar with name "%s".', $calendarNameOrId));
        }

        $this->calendar = $calendar;

        return $this->getCalendar();
    }

    /**
     * Loads and returns calendar from user and given calendar name.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function loadCalendarImageByCalendarNameYearAndMonth(string|int $userEmailOrId, string|int $calendarNameOrId, int $year, int $month, bool $clearObjects = true): CalendarImage
    {
        /* Clears all objects */
        if ($clearObjects) {
            $this->clear();
        }

        /* Load calendar */
        $calendar = $this->loadCalendar($userEmailOrId, $calendarNameOrId, false);

        /* Load calendar image */
        $calendarImage = $this->getCalendarImageRepository()->findOneByYearAndMonth($this->getUser(), $calendar, $year, $month);
        if ($calendarImage === null) {
            throw new Exception(sprintf('Unable to find calendar image with year "%d" and month "%d".', $year, $month));
        }
        $this->calendarImage = $calendarImage;

        /* Load image */
        $image = $this->calendarImage->getImage();
        if ($image === null) {
            throw new Exception(sprintf('Image not found (%s:%d).', __FILE__, __LINE__));
        }
        $this->image = $image;

        /* Returns the calendar image */
        return $this->getCalendarImage();
    }

    /**
     * Loads and returns calendar from user and given user hash, user and calendar image id.
     *
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function loadCalendarImageByUserHashAndCalendarImage(string $hash, string $userId, int $calendarImageId, bool $clearObjects = true): CalendarImage
    {
        /* Clears all objects */
        if ($clearObjects) {
            $this->clear();
        }

        /* Check that user id and user hash matches. */
        $this->userLoaderService->loadUserCheckHash($userId, $hash);

        /* Load user. */
        $user = $this->getUserRepository()->find($userId);
        if (!$user instanceof User) {
            throw new Exception(sprintf('User not found (%s:%d).', __FILE__, __LINE__));
        }

        /* Load calendar image. */
        $calendarImage = $this->getCalendarImageRepository()->findOneBy([
            'user' => $user,
            'id' => $calendarImageId,
        ]);
        if (!$calendarImage instanceof CalendarImage) {
            throw new Exception(sprintf('CalendarImage not found (%s:%d).', __FILE__, __LINE__));
        }
        $this->calendarImage = $calendarImage;

        /* Load image */
        $image = $this->calendarImage->getImage();
        if ($image === null) {
            throw new Exception(sprintf('Image not found (%s:%d).', __FILE__, __LINE__));
        }
        $this->image = $image;

        /* Returns the calendar image */
        return $this->getCalendarImage();
    }

    /**
     * Returns the user object.
     *
     * @throws Exception
     */
    public function getUser(): User
    {
        if (!isset($this->user)) {
            throw new Exception(sprintf('No user was configured (%s:%d)', __FILE__, __LINE__));
        }

        return $this->user;
    }

    /**
     * Returns the calendar object.
     *
     * @throws Exception
     */
    public function getCalendar(): Calendar
    {
        if (!isset($this->calendar)) {
            throw new Exception(sprintf('No calendar was configured (%s:%d)', __FILE__, __LINE__));
        }

        return $this->calendar;
    }

    /**
     * Returns the calendar image object.
     *
     * @throws Exception
     */
    public function getCalendarImage(): CalendarImage
    {
        if (!isset($this->calendarImage)) {
            throw new Exception(sprintf('No calendar image was configured (%s:%d)', __FILE__, __LINE__));
        }

        return $this->calendarImage;
    }

    /**
     * Returns the image object.
     *
     * @throws Exception
     */
    public function getImage(): Image
    {
        if (!isset($this->image)) {
            throw new Exception(sprintf('No image was configured (%s:%d)', __FILE__, __LINE__));
        }

        return $this->image;
    }

    /**
     * Returns a calendar given by id.
     *
     * @throws Exception
     */
    public function findOneById(int $id): Calendar
    {
        $calendar = $this->getCalendarRepository()->findOneBy([
            'id' => $id,
        ]);

        if ($calendar === null) {
            throw new Exception(sprintf('Unable to find calendar with given id "%d" (%s:%d).', $id, __FILE__, __LINE__));
        }

        return $calendar;
    }

    /**
     * Returns the UserRepository.
     *
     * @throws Exception
     */
    protected function getUserRepository(): UserRepository
    {
        $repository = $this->manager->getRepository(User::class);

        if (!$repository instanceof UserRepository) {
            throw new Exception('Error while getting UserRepository.');
        }

        return $repository;
    }

    /**
     * Returns the CalendarRepository.
     *
     * @throws Exception
     */
    protected function getCalendarRepository(): CalendarRepository
    {
        $repository = $this->manager->getRepository(Calendar::class);

        if (!$repository instanceof CalendarRepository) {
            throw new Exception('Error while getting CalendarRepository.');
        }

        return $repository;
    }

    /**
     * Returns the CalendarImageRepository.
     *
     * @throws Exception
     */
    protected function getCalendarImageRepository(): CalendarImageRepository
    {
        $repository = $this->manager->getRepository(CalendarImage::class);

        if (!$repository instanceof CalendarImageRepository) {
            throw new Exception('Error while getting CalendarImageRepository.');
        }

        return $repository;
    }

    /**
     * Clears all internal objects.
     */
    protected function clear(): void
    {
        unset($this->user);
        unset($this->calendar);
        unset($this->calendarImage);
        unset($this->image);
    }
}
