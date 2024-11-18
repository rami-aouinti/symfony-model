<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\Course\Course;
use App\CourseBundle\Entity\CGlossary;
use App\CourseBundle\Repository\CGlossaryRepository;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdateCGlossaryAction extends BaseResourceFileAction
{
    public function __invoke(CGlossary $glossary, Request $request, CGlossaryRepository $repo, EntityManager $em): CGlossary
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'];
        $description = $data['description'];
        $parentResourceNodeId = $data['parentResourceNodeId'];
        $resourceLinkList = json_decode($data['resourceLinkList'], true);
        $sid = isset($data['sid']) ? (int)$data['sid'] : null;
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
            ->andWhere('resource.title = :name')
            ->setParameter('name', $title)
        ;
        $existingGlossaryTerm = $qb->getQuery()->getOneOrNullResult();
        if ($existingGlossaryTerm !== null && $existingGlossaryTerm->getIid() !== $glossary->getIid()) {
            throw new BadRequestHttpException('The glossary term already exists.');
        }

        $glossary->setTitle($title);
        $glossary->setDescription($description);

        if (!empty($parentResourceNodeId)) {
            $glossary->setParentResourceNode($parentResourceNodeId);
        }

        if (!empty($resourceLinkList)) {
            $glossary->setResourceLinkArray($resourceLinkList);
        }

        return $glossary;
    }
}
