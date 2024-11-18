<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CourseBundle\Entity\CDocument;
use App\CourseBundle\Repository\CDocumentRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class CreateDocumentFileAction extends BaseResourceFileAction
{
    public function __invoke(Request $request, CDocumentRepository $repo, EntityManager $em, KernelInterface $kernel): CDocument
    {
        $isUncompressZipEnabled = $request->get('isUncompressZipEnabled', 'false');
        $fileExistsOption = $request->get('fileExistsOption', 'rename');

        $document = new CDocument();

        if ($isUncompressZipEnabled === 'true') {
            $result = $this->handleCreateFileRequestUncompress($document, $request, $em, $kernel);
        } else {
            $result = $this->handleCreateFileRequest($document, $repo, $request, $em, $fileExistsOption);
        }

        $document->setTitle($result['title']);
        $document->setFiletype($result['filetype']);
        $document->setComment($result['comment']);

        return $document;
    }
}
