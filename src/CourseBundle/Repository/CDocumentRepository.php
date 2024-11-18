<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Entity\Group\CGroup;
use App\Platform\Domain\Entity\ResourceNode;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

final class CDocumentRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CDocument::class);
    }

    public function getParent(CDocument $document): ?CDocument
    {
        $resourceParent = $document->getResourceNode()->getParent();

        if ($resourceParent !== null) {
            $criteria = [
                'resourceNode' => $resourceParent->getId(),
            ];

            return $this->findOneBy($criteria);
        }

        return null;
    }

    public function getFolderSize(ResourceNode $resourceNode, Course $course, ?Session $session = null): int
    {
        return $this->getResourceNodeRepository()->getSize($resourceNode, $this->getResourceType(), $course, $session);
    }

    /**
     * @return CDocument[]
     */
    public function findDocumentsByAuthor(int $userId)
    {
        $qb = $this->createQueryBuilder('d');
        $query = $qb
            ->innerJoin('d.resourceNode', 'node')
            ->innerJoin('node.resourceLinks', 'l')
            ->where('l.user = :user')
            ->setParameters([
                'user' => $userId,
            ])
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function countUserDocuments(User $user, Course $course, ?Session $session = null, ?CGroup $group = null): int
    {
        $qb = $this->getResourcesByCourseLinkedToUser($user, $course, $session, $group);

        // Add "not deleted" filters.
        $qb->select('count(resource)');

        $this->addFileTypeQueryBuilder('file', $qb);

        return $this->getCount($qb);
    }

    protected function addFileTypeQueryBuilder(string $fileType, ?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);
        $qb
            ->andWhere('resource.filetype = :filetype')
            ->setParameter('filetype', $fileType)
        ;

        return $qb;
    }
}
