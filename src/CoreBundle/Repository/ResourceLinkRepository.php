<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Entity\User\Usergroup;
use App\CourseBundle\Entity\Group\CGroup;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceLink;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

class ResourceLinkRepository extends SortableRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(ResourceLink::class));
    }

    public function remove(ResourceLink $resourceLink): void
    {
        $em = $this->getEntityManager();

        // To move the resource link at the end to reorder the list
        $resourceLink->setDisplayOrder(-1);

        $em->flush();
        // soft delete handled by Gedmo\SoftDeleteable
        $em->remove($resourceLink);
        $em->flush();
    }

    public function removeByResourceInContext(
        AbstractResource $resource,
        Course $course,
        ?Session $session = null,
        ?CGroup $group = null,
        ?Usergroup $usergroup = null,
        ?User $user = null,
    ): void {
        $link = $resource->getResourceNode()->getResourceLinkByContext($course, $session, $group, $usergroup, $user);

        if ($link) {
            $this->remove($link);
        }
    }
}
