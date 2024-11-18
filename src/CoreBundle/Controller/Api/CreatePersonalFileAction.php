<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\User\PersonalFile;
use App\CoreBundle\Repository\Node\PersonalFileRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class CreatePersonalFileAction extends BaseResourceFileAction
{
    public function __invoke(Request $request, PersonalFileRepository $repo, EntityManager $em): PersonalFile
    {
        $resource = new PersonalFile();
        $result = $this->handleCreateFileRequest($resource, $repo, $request, $em, 'overwrite');

        $resource->setTitle($result['title']);

        return $resource;
    }
}
