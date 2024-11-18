<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\ScheduledAnnouncement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScheduledAnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledAnnouncement::class);
    }

    /**
     * Mark an announcement as sent.
     */
    public function markAsSent(ScheduledAnnouncement $announcement): void
    {
        $announcement->setSent(true);
        $this->_em->persist($announcement);
        $this->_em->flush();
    }
}
