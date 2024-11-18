<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\Calendar\Domain\Entity\CCalendarEventAttachment;
use App\CoreBundle\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CCalendarEventAttachmentRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCalendarEventAttachment::class);
    }
}
