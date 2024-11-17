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

use App\Calendar\Domain\Entity\HolidayGroup;
use App\Calendar\Infrastructure\Repository\HolidayGroupRepository;
use App\Platform\Application\Service\Entity\Base\BaseLoaderService;
use App\User\Application\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-01-02)
 * @package App\Command
 */
class HolidayGroupLoaderService extends BaseLoaderService
{
    protected HolidayGroup $holidayGroup;

    public function __construct(
        protected KernelInterface $appKernel,
        protected EntityManagerInterface $manager,
        protected SecurityService $securityService
    ) {
    }

    /**
     * Loads and returns calendar from user.
     *
     * @throws Exception
     */
    public function loadHolidayGroup(string $holidayGroupName): HolidayGroup
    {
        /* Clears all objects */
        $this->clear();

        /* Load user */
        $holidayGroup = $this->getHolidayGroupRepository()->findOneByName($holidayGroupName);
        if ($holidayGroup === null) {
            throw new Exception(sprintf('Unable to find holiday group with name "%s".', $holidayGroupName));
        }
        $this->holidayGroup = $holidayGroup;

        /* Returns the holiday group */
        return $this->getHolidayGroup();
    }

    /**
     * Returns the holiday group object.
     */
    public function getHolidayGroup(): HolidayGroup
    {
        return $this->holidayGroup;
    }

    /**
     * Returns a holiday group given by id.
     *
     * @throws Exception
     */
    public function findOneById(int $id): HolidayGroup
    {
        $holidayGroup = $this->getHolidayGroupRepository()->findOneBy([
            'id' => $id,
        ]);

        if ($holidayGroup === null) {
            throw new Exception(sprintf('Unable to find holiday group with given id "%d" (%s:%d).', $id, __FILE__, __LINE__));
        }

        return $holidayGroup;
    }

    /**
     * Returns the HolidayGroupRepository.
     *
     * @throws Exception
     */
    protected function getHolidayGroupRepository(): HolidayGroupRepository
    {
        $repository = $this->manager->getRepository(HolidayGroup::class);

        if (!$repository instanceof HolidayGroupRepository) {
            throw new Exception('Error while getting HolidayGroup.');
        }

        return $repository;
    }

    /**
     * Clears all internal objects.
     */
    protected function clear(): void
    {
        unset($this->holidayGroup);
    }
}
