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

namespace App\Calendar\Transport\EventListener\Entity;

use App\Calendar\Domain\Entity\Holiday;
use App\Platform\Domain\Entity\EntityInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.1 (2022-11-11)
 * @since 1.0.1 (2022-11-11) Refactoring.
 * @since 1.0.0 (2022-04-03) First version.
 * @package App\EventListener\Entity
 */
class HolidayListener
{
    /**
     * Pre persist.
     *
     * @template EntityObject of EntityInterface
     * @template EventObject of LifecycleEventArgs
     *
     * @throws Exception
     */
    #[ORM\PrePersist]
    public function prePersistHandler(EntityInterface $entity, LifecycleEventArgs $event): void
    {
        if (!$entity instanceof Holiday) {
            return;
        }

        $this->setDate($entity);
    }

    /**
     * Pre update.
     *
     * @template EntityObject of EntityInterface
     * @template EventObject of LifecycleEventArgs
     *
     * @throws Exception
     */
    #[ORM\PreUpdate]
    public function preUpdateHandler(EntityInterface $entity, LifecycleEventArgs $event): void
    {
        if (!$entity instanceof Holiday) {
            return;
        }

        $this->setDate($entity);
    }
    /**
     * Replaces the year with 1970.
     *
     * @throws Exception
     */
    protected function setDate(Holiday $holiday): void
    {
        if (!$holiday->getYearly()) {
            return;
        }

        $day = $holiday->getDate()->format('d');
        $month = $holiday->getDate()->format('m');
        $year = 1970;

        $dateString = sprintf('%d-%s-%s', $year, $month, $day);

        $date = DateTime::createFromFormat('Y-m-d', $dateString);

        if (!$date instanceof DateTime) {
            throw new Exception(sprintf('Unable to parse given date (%s:%d).', __FILE__, __LINE__));
        }

        $holiday->setDate($date);
    }
}
