<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CoreBundle\Entity\User\PersonalFile;
use App\CoreBundle\Repository\Node\PersonalFileRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class UpdatePersonalFileAction extends BaseResourceFileAction
{
    public function __invoke(PersonalFile $resource, Request $request, PersonalFileRepository $repo, EntityManager $em): PersonalFile
    {
        $this->handleUpdateRequest($resource, $repo, $request, $em);

        return $resource;
    }
}
