<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CourseBundle\Entity\CLinkCategory;
use App\CourseBundle\Repository\CLinkRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package App\CoreBundle\Controller\Api
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class CreateCLinkCategoryAction extends BaseResourceFileAction
{
    public function __invoke(Request $request, CLinkRepository $repo, EntityManager $em): CLinkCategory
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['category_title'];
        $description = $data['description'];
        $parentResourceNodeId = $data['parentResourceNodeId'];
        $resourceLinkList = json_decode($data['resourceLinkList'], true);

        $linkCategory = (new CLinkCategory())
            ->setTitle($title)
            ->setDescription($description)
        ;

        if (!empty($parentResourceNodeId)) {
            $linkCategory->setParentResourceNode($parentResourceNodeId);
        }

        if (!empty($resourceLinkList)) {
            $linkCategory->setResourceLinkArray($resourceLinkList);
        }

        return $linkCategory;
    }
}
