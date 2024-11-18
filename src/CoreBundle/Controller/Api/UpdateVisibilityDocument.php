<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Repository\CDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateVisibilityDocument extends AbstractController
{
    public function __invoke(CDocument $document, CDocumentRepository $repo): CDocument
    {
        $repo->toggleVisibilityPublishedDraft($document);

        return $document;
    }
}
