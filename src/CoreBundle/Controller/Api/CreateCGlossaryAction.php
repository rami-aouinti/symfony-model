<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\Course\Course;
use App\CourseBundle\Entity\CGlossary;
use App\CourseBundle\Repository\CGlossaryRepository;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateCGlossaryAction extends BaseResourceFileAction
{
    public function __invoke(Request $request, CGlossaryRepository $repo, EntityManager $em): CGlossary
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'];
        $description = $data['description'];
        $parentResourceNodeId = $data['parentResourceNodeId'];
        $resourceLinkList = json_decode($data['resourceLinkList'], true);
        $sid = isset($data['sid']) ? (int)$data['sid'] : 0;
        $cid = (int)$data['cid'];

        $course = null;
        $session = null;
        if ($cid !== 0) {
            $course = $em->getRepository(Course::class)->find($cid);
        }
        if ($sid !== 0) {
            $session = $em->getRepository(Session::class)->find($sid);
        }

        // Check if the term already exists
        $qb = $repo->getResourcesByCourse($course, $session)
            ->andWhere('resource.title = :title')
            ->setParameter('title', $title)
        ;
        $existingGlossaryTerm = $qb->getQuery()->getOneOrNullResult();
        if ($existingGlossaryTerm !== null) {
            throw new BadRequestHttpException('The glossary term already exists.');
        }

        $glossary = (new CGlossary())
            ->setTitle($title)
            ->setDescription($description)
        ;

        if (!empty($parentResourceNodeId)) {
            $glossary->setParentResourceNode($parentResourceNodeId);
        }

        if (!empty($resourceLinkList)) {
            $glossary->setResourceLinkArray($resourceLinkList);
        }

        return $glossary;
    }
}
