<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository\Node;

use App\CoreBundle\Repository\ResourceRepository;
use App\Message\Domain\Entity\MessageAttachment;
use Doctrine\Persistence\ManagerRegistry;

final class MessageAttachmentRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageAttachment::class);
    }

    /*public function getResources(User $user, ResourceNode $parentNode, Course $course = null, Session $session = null, CGroup $group = null): QueryBuilder
    {
        return $this->getResourcesByCreator($user, $parentNode);
    }*/
}
