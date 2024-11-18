<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\CShortcut;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\Persistence\ManagerRegistry;

final class CShortcutRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CShortcut::class);
    }

    public function getShortcutFromResource(ResourceInterface $resource): ?CShortcut
    {
        $criteria = [
            'shortCutNode' => $resource->getResourceNode(),
        ];

        return $this->findOneBy($criteria);
    }

    public function addShortCut(ResourceInterface $resource, User $user, Course $course, ?Session $session = null): CShortcut
    {
        $shortcut = $this->getShortcutFromResource($resource);

        if ($shortcut === null) {
            $shortcut = (new CShortcut())
                ->setTitle($resource->getResourceName())
                ->setShortCutNode($resource->getResourceNode())
                ->setCreator($user)
                ->setParent($course)
                ->addCourseLink($course, $session)
            ;

            $this->create($shortcut);
        }

        return $shortcut;
    }

    public function removeShortCut(ResourceInterface $resource): bool
    {
        $em = $this->getEntityManager();
        $shortcut = $this->getShortcutFromResource($resource);
        if ($shortcut !== null) {
            $em->remove($shortcut);
            // $em->flush();

            return true;
        }

        return false;
    }
}
