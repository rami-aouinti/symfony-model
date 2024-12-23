<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Listener;

use App\CoreBundle\Repository\ResourceNodeRepository;
use App\CoreBundle\Tool\ToolChain;
use App\Platform\Domain\Entity\ResourceNode;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class ResourceNodeListener
{
    // protected $accessUrl;

    public function __construct(
        protected SlugifyInterface $slugify,
        protected ToolChain $toolChain,
        protected RequestStack $request,
        protected Security $security,
        protected ResourceNodeRepository $resourceNodeRepository
    ) {
    }

    /*public function prePersist(ResourceNode $resourceNode, LifecycleEventArgs $event)
    {
        return true;
    }*/

    /**
     * When updating a Resource.
     * $resourceNode->getContent() was set in the BaseResourceFileAction (when calling the api).
     */
    public function preUpdate(ResourceNode $resourceNode, PreUpdateEventArgs $event)
    {
        if ($resourceNode->hasEditableTextContent()) {
            $resourceFile = $resourceNode->getResourceFiles()->first();
            $fileName = $this->resourceNodeRepository->getFilename($resourceFile);
            if ($fileName) {
                $content = $resourceNode->getContent();
                // Skip saving null.
                if ($content !== null) {
                    $this->resourceNodeRepository->getFileSystem()->write($fileName, $content);
                }
            }
        }

        return true;
    }

    /*public function postUpdate(ResourceNode $resourceNode, LifecycleEventArgs $event): void
    {
    }*/
}
