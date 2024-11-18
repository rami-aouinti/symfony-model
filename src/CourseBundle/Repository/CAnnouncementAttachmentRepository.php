<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\Announcement\Domain\Entity\CAnnouncementAttachment;
use App\CoreBundle\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CAnnouncementAttachmentRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CAnnouncementAttachment::class);
    }
}
