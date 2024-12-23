<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\CoreBundle\Tool\AbstractTool;
use App\CoreBundle\Tool\ToolChain;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class ResourceFactory
{
    protected ToolChain $toolChain;

    protected EntityManagerInterface $entityManager;

    public function __construct(ToolChain $toolChain, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->toolChain = $toolChain;
    }

    public function getRepositoryService(string $toolName, string $type)
    {
        $tool = $this->toolChain->getToolFromName($toolName);

        if (!$tool instanceof AbstractTool) {
            throw new InvalidArgumentException(\sprintf('Tool %s not found', $toolName));
        }

        $entityClass = $tool->getEntityByResourceType($type);

        if ($entityClass === null) {
            throw new InvalidArgumentException(\sprintf('Entity not found for tool "%s" and type "%s" ', $toolName, $type));
        }

        return $this->entityManager->getRepository($entityClass);
    }
}
