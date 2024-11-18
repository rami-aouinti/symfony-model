<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository\Node;

use App\Blog\Domain\Entity\SocialPostAttachment;
use App\CoreBundle\Repository\ResourceRepository;
use Doctrine\Persistence\ManagerRegistry;

class SocialPostAttachmentRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialPostAttachment::class);
    }
}
