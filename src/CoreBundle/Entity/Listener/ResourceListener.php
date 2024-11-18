<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Listener;

use App\Access\Domain\Entity\AccessUrl;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Controller\Api\BaseResourceFileAction;
use App\CoreBundle\Entity\User\PersonalFile;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Tool\ToolChain;
use App\CoreBundle\Traits\AccessUrlListenerTrait;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\EntityAccessUrlInterface;
use App\Platform\Domain\Entity\ResourceFile;
use App\Platform\Domain\Entity\ResourceFormat;
use App\Platform\Domain\Entity\ResourceLink;
use App\Platform\Domain\Entity\ResourceNode;
use App\Platform\Domain\Entity\ResourceToRootInterface;
use App\Platform\Domain\Entity\ResourceType;
use App\Platform\Domain\Entity\ResourceWithAccessUrlInterface;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

use const JSON_THROW_ON_ERROR;
use const PATHINFO_EXTENSION;

class ResourceListener
{
    use AccessUrlListenerTrait;

    public function __construct(
        protected SlugifyInterface $slugify,
        protected ToolChain $toolChain,
        protected RequestStack $request,
        protected Security $security
    ) {
    }

    /**
     * Only in creation.
     *
     * @throws Exception
     */
    public function prePersist(AbstractResource $resource, PrePersistEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $request = $this->request;

        // 1. Set AccessUrl.
        if ($resource instanceof ResourceWithAccessUrlInterface) {
            // Checking if this resource is connected with a AccessUrl.
            if ($resource->getUrls()->count() === 0) {
                // The AccessUrl was not added using $resource->addAccessUrl(),
                // Try getting the URL from the session bag if possible.
                $accessUrl = $this->getAccessUrl($em, $request);
                if ($accessUrl === null) {
                    throw new Exception('This resource needs an AccessUrl use $resource->addAccessUrl();');
                }
                $resource->addAccessUrl($accessUrl);
            }
        }

        // This will attach the resource to the main resource node root (For example a Course).
        if ($resource instanceof ResourceToRootInterface) {
            $accessUrl = $this->getAccessUrl($em, $request);
            $resource->setParent($accessUrl);
        }

        // 2. Set creator.
        // Check if creator was set with $resource->setCreator()
        $creator = $resource->getResourceNodeCreator();

        $currentUser = null;
        if ($creator === null) {
            // Get the creator from the current request.
            /** @var User|null $currentUser */
            $currentUser = $this->security->getUser();
            if ($currentUser !== null) {
                $creator = $currentUser;
            }

            // Check if user has a resource node.
            if ($resource->hasResourceNode() && $resource->getCreator() !== null) {
                $creator = $resource->getCreator();
            }
        }

        if ($creator === null) {
            throw new UserNotFoundException('User creator not found, use $resource->setCreator();');
        }

        // 3. Set ResourceType.
        // @todo use static table instead of Doctrine
        $resourceTypeRepo = $em->getRepository(ResourceType::class);
        $entityClass = $eventArgs->getObject()::class;

        $name = $this->toolChain->getResourceTypeNameByEntity($entityClass);

        $resourceType = $resourceTypeRepo->findOneBy([
            'title' => $name,
        ]);

        if ($resourceType === null) {
            throw new InvalidArgumentException(\sprintf('ResourceType: "%s" not found for entity "%s"', $name, $entityClass));
        }

        // 4. Set ResourceNode parent.
        // Add resource directly to the resource node root (Example: a Course resource).
        $parentNode = null;
        if ($resource instanceof ResourceWithAccessUrlInterface) {
            $parentUrl = null;
            if ($resource->getUrls()->count() > 0) {
                $urlRelResource = $resource->getUrls()->first();
                if (!$urlRelResource instanceof EntityAccessUrlInterface) {
                    $msg = '$resource->getUrls() must return a Collection that implements EntityAccessUrlInterface';

                    throw new InvalidArgumentException($msg);
                }
                if (!$urlRelResource->getUrl()->hasResourceNode()) {
                    $msg = 'An item from the Collection $resource->getUrls() must implement EntityAccessUrlInterface.';

                    throw new InvalidArgumentException($msg);
                }
                $parentUrl = $urlRelResource->getUrl()->getResourceNode();
            }

            if ($parentUrl === null) {
                throw new InvalidArgumentException('The resource needs an AccessUrl: use $resource->addAccessUrl()');
            }
            $parentNode = $parentUrl;
        }

        // Reads the parentResourceNodeId parameter set in BaseResourceFileAction.php
        if ($resource->hasParentResourceNode()) {
            $nodeRepo = $em->getRepository(ResourceNode::class);
            $parent = $nodeRepo->find($resource->getParentResourceNode());
            if ($parent !== null) {
                $parentNode = $parent;
            }
        }

        if ($parentNode === null) {
            // Try getting the parent node from the resource.
            if ($resource->getParent() !== null) {
                $parentNode = $resource->getParent()->getResourceNode();
            }
        }

        // Last chance check parentResourceNodeId from request.
        if ($request !== null && $parentNode === null) {
            $currentRequest = $request->getCurrentRequest();
            if ($currentRequest !== null) {
                $resourceNodeIdFromRequest = $currentRequest->get('parentResourceNodeId');
                if (empty($resourceNodeIdFromRequest)) {
                    $contentData = $request->getCurrentRequest()->getContent();
                    $contentData = json_decode($contentData, true, 512, JSON_THROW_ON_ERROR);
                    $resourceNodeIdFromRequest = $contentData['parentResourceNodeId'] ?? '';
                }

                if (!empty($resourceNodeIdFromRequest)) {
                    $nodeRepo = $em->getRepository(ResourceNode::class);
                    $parent = $nodeRepo->find($resourceNodeIdFromRequest);
                    if ($parent !== null) {
                        $parentNode = $parent;
                    }
                }
            }
        }

        if ($parentNode === null && !$resource instanceof AccessUrl) {
            $msg = \sprintf('Resource %s needs a parent', $resource->getResourceName());

            throw new InvalidArgumentException($msg);
        }

        if ($resource instanceof PersonalFile) {
            if ($currentUser === null) {
                $currentUser = $parentNode->getCreator();
            }
            $valid = $parentNode->getCreator()->getUsername() === $currentUser->getUsername()
                     || $parentNode->getId() === $currentUser->getResourceNode()->getId();

            if (!$valid) {
                $msg = \sprintf('User %s cannot add a file to another user', $currentUser->getUsername());

                throw new InvalidArgumentException($msg);
            }
        }

        // 4. Create ResourceNode for the Resource
        $resourceNode = (new ResourceNode())
            ->setCreator($creator)
            ->setResourceType($resourceType)
            ->setParent($parentNode)
        ;

        $txtTypes = [
            'events',
            'event_attachments',
            'illustrations',
            'links',
            'files',
            'courses',
            'users',
            'external_tools',
            'usergroups',
        ];
        $resourceFormatRepo = $em->getRepository(ResourceFormat::class);
        $formatName = (\in_array($name, $txtTypes, true) ? 'txt' : 'html');
        $resourceFormat = $resourceFormatRepo->findOneBy([
            'title' => $formatName,
        ]);
        if ($resourceFormat) {
            $resourceNode->setResourceFormat($resourceFormat);
        }

        $resource->setResourceNode($resourceNode);

        // Update resourceNode title from Resource.
        $this->updateResourceName($resource);

        BaseResourceFileAction::setLinks($resource, $em);

        // Upload File was set in BaseResourceFileAction.php
        if ($resource->hasUploadFile()) {
            $uploadedFile = $resource->getUploadFile();

            // File upload.
            if ($uploadedFile instanceof UploadedFile) {
                $resourceFile = (new ResourceFile())
                    ->setTitle($uploadedFile->getFilename())
                    ->setOriginalName($uploadedFile->getFilename())
                    ->setFile($uploadedFile)
                ;
                $resourceNode->addResourceFile($resourceFile);
                $em->persist($resourceNode);
            }
        }

        $resource->setResourceNode($resourceNode);

        // All resources should have a parent, except AccessUrl.
        if (!($resource instanceof AccessUrl) && $resourceNode->getParent() === null) {
            $message = \sprintf(
                'ResourceListener: Resource %s, has a resource node, but this resource node must have a parent',
                $resource->getResourceName()
            );

            throw new InvalidArgumentException($message);
        }

        if ($resource instanceof CCalendarEvent) {
            $this->addCCalendarEventGlobalLink($resource, $eventArgs);
        }
    }

    /**
     * When updating a Resource.
     */
    public function preUpdate(AbstractResource $resource, PreUpdateEventArgs $eventArgs): void
    {
        $resourceNode = $resource->getResourceNode();
        $parentResourceNode = $resource->getParent()?->resourceNode;

        if ($parentResourceNode) {
            $resourceNode->setParent($parentResourceNode);
        }

        // error_log('Resource listener preUpdate');
        // $this->setLinks($resource, $eventArgs->getEntityManager());
    }

    public function postUpdate(AbstractResource $resource, PostUpdateEventArgs $eventArgs): void
    {
        // error_log('resource listener postUpdate');
        // $em = $eventArgs->getEntityManager();
        // $this->updateResourceName($resource, $resource->getResourceName(), $em);
    }

    public function updateResourceName(AbstractResource $resource): void
    {
        $resourceName = $resource->getResourceName();

        if (empty($resourceName)) {
            throw new InvalidArgumentException('Resource needs a name');
        }

        $extension = $this->slugify->slugify(pathinfo($resourceName, PATHINFO_EXTENSION));
        if (empty($extension)) {
            // $slug = $this->slugify->slugify($resourceName);
        }
        /*$originalExtension = pathinfo($resourceName, PATHINFO_EXTENSION);
        $originalBasename = \basename($resourceName, $originalExtension);
        $slug = sprintf('%s.%s', $this->slugify->slugify($originalBasename), $originalExtension);*/
        $resource->getResourceNode()->setTitle($resourceName);
    }

    private function addCCalendarEventGlobalLink(CCalendarEvent $event, PrePersistEventArgs $eventArgs): void
    {
        $currentRequest = $this->request->getCurrentRequest();

        if ($currentRequest === null) {
            return;
        }

        $type = $currentRequest->query->get('type');
        if ($type === null) {
            $content = $currentRequest->getContent();
            $params = json_decode($content, true);
            if (isset($params['isGlobal']) && (int)$params['isGlobal'] === 1) {
                $type = 'global';
            }
        }

        if ($type === 'global') {
            $em = $eventArgs->getObjectManager();
            $resourceNode = $event->getResourceNode();

            $globalLink = new ResourceLink();
            $globalLink->setCourse(null)
                ->setSession(null)
                ->setGroup(null)
                ->setUser(null)
            ;

            $alreadyHasGlobalLink = false;
            foreach ($resourceNode->getResourceLinks() as $existingLink) {
                if (
                    $existingLink->getCourse() === null && $existingLink->getSession() === null
                    && $existingLink->getGroup() === null && $existingLink->getUser() === null
                ) {
                    $alreadyHasGlobalLink = true;

                    break;
                }
            }

            if (!$alreadyHasGlobalLink) {
                $resourceNode->addResourceLink($globalLink);
                $em->persist($globalLink);
            }
        }
    }
}
