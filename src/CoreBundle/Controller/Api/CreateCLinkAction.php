<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CourseBundle\Entity\CLink;
use App\CourseBundle\Entity\CLinkCategory;
use App\CourseBundle\Repository\CLinkRepository;
use App\CourseBundle\Repository\CShortcutRepository;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class CreateCLinkAction extends BaseResourceFileAction
{
    public function __invoke(Request $request, CLinkRepository $repo, EntityManager $em, CShortcutRepository $shortcutRepository, Security $security): CLink
    {
        $data = json_decode($request->getContent(), true);
        $url = $data['url'];
        $title = $data['title'];
        $description = $data['description'];
        $categoryId = (int)$data['category'];
        $onHomepage = isset($data['showOnHomepage']) && (bool)$data['showOnHomepage'];
        $target = $data['target'];
        $parentResourceNodeId = $data['parentResourceNodeId'];
        $resourceLinkList = json_decode($data['resourceLinkList'], true);

        $link = (new CLink())
            ->setUrl($url)
            ->setTitle($title)
            ->setDescription($description)
            ->setTarget($target)
        ;

        if ($categoryId !== 0) {
            $linkCategory = $em
                ->getRepository(CLinkCategory::class)
                ->find($categoryId)
            ;

            if ($linkCategory) {
                $link->setCategory($linkCategory);
            }
        }

        if (!empty($parentResourceNodeId)) {
            $link->setParentResourceNode($parentResourceNodeId);
        }

        if (!empty($resourceLinkList)) {
            $link->setResourceLinkArray($resourceLinkList);
        }

        $em->persist($link);
        $em->flush();

        $this->handleShortcutCreation($resourceLinkList, $em, $security, $link, $shortcutRepository, $onHomepage);

        return $link;
    }

    private function handleShortcutCreation(
        array $resourceLinkList,
        EntityManager $em,
        Security $security,
        CLink $link,
        CShortcutRepository $shortcutRepository,
        bool $onHomepage
    ): void {
        $firstLink = reset($resourceLinkList);
        if (isset($firstLink['sid'], $firstLink['cid'])) {
            $sid = $firstLink['sid'];
            $cid = $firstLink['cid'];
            $course = $cid ? $em->getRepository(Course::class)->find($cid) : null;
            $session = $sid ? $em->getRepository(Session::class)->find($sid) : null;

            /** @var User $currentUser */
            $currentUser = $security->getUser();
            if ($onHomepage) {
                $shortcutRepository->addShortCut($link, $currentUser, $course, $session);
            }
        }
    }
}
