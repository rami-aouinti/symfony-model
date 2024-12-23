<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\Course\Course;
use App\CourseBundle\Entity\CGlossary;
use App\CourseBundle\Repository\CGlossaryRepository;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetGlossaryCollectionController extends BaseResourceFileAction
{
    public function __invoke(Request $request, CGlossaryRepository $repo, EntityManager $em): Response
    {
        $cid = $request->query->getInt('cid');
        $sid = $request->query->getInt('sid');
        $q = $request->query->get('q');
        $course = null;
        $session = null;
        if ($cid) {
            $course = $em->getRepository(Course::class)->find($cid);
        }

        if ($sid) {
            $session = $em->getRepository(Session::class)->find($sid);
        }

        $qb = $repo->getResourcesByCourse($course, $session, null, null, true, true);
        if ($q) {
            $qb->andWhere($qb->expr()->like('resource.title', ':title'))
                ->setParameter('title', '%' . $q . '%')
            ;
        }
        $glossaries = $qb->getQuery()->getResult();

        $dataResponse = [];
        if ($glossaries) {
            /** @var CGlossary $item */
            foreach ($glossaries as $item) {
                $dataResponse[] =
                    [
                        'iid' => $item->getIid(),
                        'id' => $item->getIid(),
                        'title' => $item->getTitle(),
                        'description' => $item->getDescription(),
                        'sessionId' => $item->getFirstResourceLink()->getSession() ? $item->getFirstResourceLink()->getSession()->getId() : null,
                    ];
            }
        }

        return new JsonResponse($dataResponse);
    }
}
