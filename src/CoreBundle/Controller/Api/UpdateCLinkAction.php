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

class UpdateCLinkAction extends BaseResourceFileAction
{
    public function __invoke(CLink $link, Request $request, CLinkRepository $repo, EntityManager $em, CShortcutRepository $shortcutRepository, Security $security): CLink
    {
        $data = json_decode($request->getContent(), true);
        $url = $data['url'];
        $title = $data['title'];
        $description = $data['description'];
        $categoryId = (int)$data['category'];
        $onHomepage = isset($data['showOnHomepage']) && (bool)$data['showOnHomepage'];
        $target = $data['target'];
        $resourceLinkList = json_decode($data['resourceLinkList'], true);

        $link->setUrl($url);
        $link->setTitle($title);
        $link->setDescription($description);
        $link->setTarget($target);

        if ($categoryId !== 0) {
            $linkCategory = $em->getRepository(CLinkCategory::class)->find($categoryId);
            if ($linkCategory) {
                $link->setCategory($linkCategory);
            }
        }

        $em->persist($link);
        $em->flush();

        $this->handleShortcutCreationOrDeletion($resourceLinkList, $em, $security, $link, $shortcutRepository, $onHomepage);

        return $link;
    }

    private function handleShortcutCreationOrDeletion(
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
                $shorcut = $shortcutRepository->addShortCut($link, $currentUser, $course, $session);
            } else {
                $removed = $shortcutRepository->removeShortCut($link);
            }
        }
    }
}
