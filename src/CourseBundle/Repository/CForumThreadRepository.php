<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Repository\ResourceRepository;
use App\Forum\Domain\Entity\CForumThread;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CForumThreadRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CForumThread::class);
    }

    public function getForumThread(string $title, Course $course, ?Session $session = null): ?CForumThread
    {
        $qb = $this->getResourcesByCourse($course, $session);
        $qb
            ->andWhere('resource.title = :title')
            ->setParameter('title', $title)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByCourse(
        Course $course,
        ?Session $session = null,
        ?string $title = null
    ): QueryBuilder {
        $qb = $this->getResourcesByCourse($course, $session);

        $this->addTitleQueryBuilder($title, $qb);

        return $qb;
    }

    public function increaseView(CForumThread $thread): void
    {
        $thread->setThreadViews($thread->getThreadViews() + 1);
        $em = $this->getEntityManager();
        $em->persist($thread);
        $em->flush();
    }

    public function delete(ResourceInterface $resource): void
    {
        /** @var CForumThread $resource */
        $posts = $resource->getPosts();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                parent::delete($post);
            }
        }
        parent::delete($resource);
    }

    public function getThreadsBySubscriptions(int $userId, int $courseId): array
    {
        $qb = $this->createQueryBuilder('thread')
            ->where('thread.iid IN (
            SELECT fn.threadId
            FROM App\CourseBundle\Entity\CForumNotification fn
            WHERE fn.cId = :courseId AND fn.userId = :userId
        )')
            ->setParameter('courseId', $courseId)
            ->setParameter('userId', $userId)
        ;

        return $qb->getQuery()->getResult();
    }
}
