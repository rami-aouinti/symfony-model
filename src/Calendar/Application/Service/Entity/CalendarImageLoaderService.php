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
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Service\Entity\Base\BaseLoaderService;
use App\User\Application\Service\Entity\UserLoaderService;
use App\User\Application\Service\SecurityService;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-04-18)
 * @package App\Command
 */
class CalendarImageLoaderService extends BaseLoaderService
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
     * Returns a calendar image given by id.
     *
     * @throws Exception
     */
    public function findOneById(int $id): CalendarImage
    {
        $calendarImage = $this->getCalendarImageRepository()->findOneBy([
            'id' => $id,
        ]);

        if ($calendarImage === null) {
            throw new Exception(sprintf('Unable to find calendar image with given id "%d" (%s:%d).', $id, __FILE__, __LINE__));
        }

        return $calendarImage;
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
}
