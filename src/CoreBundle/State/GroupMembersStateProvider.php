<?php

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\CoreBundle\Entity\User\Usergroup;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template-implements ProviderInterface<Usergroup>
 */
final class GroupMembersStateProvider implements ProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Operation $operation, array $uriVariables = [], array $context = []): bool
    {
        return $operation->getClass() === Usergroup::class && $operation->getName() === 'get_group_members';
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $groupId = $uriVariables['id'] ?? null;

        if ($groupId === null) {
            return [];
        }

        $usergroupRepository = $this->entityManager->getRepository(Usergroup::class);

        return $usergroupRepository->getUsersByGroup((int)$groupId);
    }
}
