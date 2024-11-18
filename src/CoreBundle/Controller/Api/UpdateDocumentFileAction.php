<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Repository\CDocumentRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class UpdateDocumentFileAction extends BaseResourceFileAction
{
    public function __invoke(CDocument $document, Request $request, CDocumentRepository $repo, EntityManager $em): CDocument
    {
        $this->handleUpdateRequest($document, $repo, $request, $em);

        return $document;
    }
}
