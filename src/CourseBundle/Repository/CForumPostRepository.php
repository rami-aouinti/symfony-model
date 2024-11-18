<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\ResourceRepository;
use App\Forum\Domain\Entity\CForumPost;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\Persistence\ManagerRegistry;

class CForumPostRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CForumPost::class);
    }

    public function countCourseForumPosts(Course $course, ?Session $session = null): int
    {
        $qb = $this->getResourcesByCourse($course, $session);

        return $this->getCount($qb);
    }

    public function countUserForumPosts(User $user, Course $course, ?Session $session = null): int
    {
        $qb = $this->getResourcesByCourseLinkedToUser($user, $course, $session);

        return $this->getCount($qb);
    }

    /*public function findAllInCourseByThread(
     * bool $onlyVisible,
     * bool $isAllowedToEdit,
     * CForumThread $thread,
     * Course $course,
     * User $currentUser = null,
     * CGroup $group = null,
     * string $orderDirection = 'ASC'
     * ): array {
     * $conditionVisibility = $onlyVisible ? 'p.visible = 1' : 'p.visible != 2';
     * $conditionModerated = '';
     * $filterModerated = true;
     * if (
     * (empty($group) && $isAllowedToEdit) ||
     * (
     * (null !== $group ? $group->hasTutor($currentUser) : false) ||
     * !$onlyVisible
     * )
     * ) {
     * $filterModerated = false;
     * }
     * if ($filterModerated && $onlyVisible && $thread->getForum()->isModerated()) {
     * $userId = null !== $currentUser ? $currentUser->getId() : 0;
     * $conditionModerated = 'AND p.status = 1 OR
     * (p.status = '.CForumPost::STATUS_WAITING_MODERATION." AND p.posterId = {$userId}) OR
     * (p.status = ".CForumPost::STATUS_REJECTED." AND p.poster = {$userId}) OR
     * (p.status IS NULL AND p.posterId = {$userId})";
     * }
     * $dql = "SELECT p
     * FROM ChamiloCourseBundle:CForumPost p
     * WHERE
     * p.thread = :thread AND
     * p.cId = :course AND
     * {$conditionVisibility}
     * {$conditionModerated}
     * ORDER BY p.iid {$orderDirection}";
     * return $this
     * ->getEntityManager()
     * ->createQuery($dql)
     * ->setParameters([
     * 'thread' => $thread,
     * 'course' => $course,
     * ])
     * ->getResult()
     * ;
     * }*/
    public function delete(ResourceInterface $resource): void
    {
        /** @var CForumPost $resource */
        $attachments = $resource->getAttachments();

        foreach ($attachments as $attachment) {
            $this->getEntityManager()->remove($attachment);
        }

        parent::delete($resource);
    }
}
