<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Repository\ResourceRepository;
use App\CoreBundle\Repository\ResourceWithLinkInterface;
use App\CourseBundle\Entity\CLp\CLp;
use App\CourseBundle\Entity\CLp\CLpItem;
use App\Forum\Domain\Entity\CForum;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Routing\RouterInterface;

final class CLpRepository extends ResourceRepository implements ResourceWithLinkInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CLp::class);
    }

    public function createLp(CLp $lp): void
    {
        if ($lp->getResourceNode() !== null) {
            throw new Exception('Lp should not have a resource node during creation');
        }

        $lpItem = (new CLpItem())
            ->setTitle('root')
            ->setPath('root')
            ->setLp($lp)
            ->setItemType('root')
        ;
        $lp->getItems()->add($lpItem);
        $this->create($lp);
    }

    public function findForumByCourse(CLp $lp, Course $course, ?Session $session = null): ?CForum
    {
        $forums = $lp->getForums();
        $result = null;
        foreach ($forums as $forum) {
            $links = $forum->getResourceNode()->getResourceLinks();
            foreach ($links as $link) {
                if ($link->getCourse() === $course && $link->getSession() === $session) {
                    $result = $forum;

                    break 2;
                }
            }
        }

        return $result;
    }

    public function findAllByCourse(
        Course $course,
        ?Session $session = null,
        ?string $title = null,
        ?int $active = null,
        bool $onlyPublished = true,
        ?int $categoryId = null
    ): QueryBuilder {
        $qb = $this->getResourcesByCourse($course, $session);

        /*if ($onlyPublished) {
            $this->addDateFilterQueryBuilder(new DateTime(), $qb);
        }*/
        // $this->addCategoryQueryBuilder($categoryId, $qb);
        // $this->addActiveQueryBuilder($active, $qb);
        // $this->addNotDeletedQueryBuilder($qb);
        $this->addTitleQueryBuilder($title, $qb);

        return $qb;
    }

    public function getLink(ResourceInterface $resource, RouterInterface $router, array $extraParams = []): string
    {
        $params = [
            'lp_id' => $resource->getResourceIdentifier(),
            'name' => 'lp/lp_controller.php',
            'action' => 'view',
        ];
        if (!empty($extraParams)) {
            $params = array_merge($params, $extraParams);
        }

        return $router->generate('legacy_main', $params);
    }

    protected function addNotDeletedQueryBuilder(?QueryBuilder $qb = null): QueryBuilder
    {
        $qb = $this->getOrCreateQueryBuilder($qb);

        $qb->andWhere('resource.active <> -1');

        return $qb;
    }
}
