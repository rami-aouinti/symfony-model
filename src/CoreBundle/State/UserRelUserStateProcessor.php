<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\CoreBundle\Entity\User\UserRelUser;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\CoreBundle\State
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserRelUserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly ProcessorInterface $removeProcessor,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?UserRelUser
    {
        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        \assert($result instanceof UserRelUser);

        if ($operation instanceof Put) {
            if ($data->getRelationType() === UserRelUser::USER_RELATION_TYPE_FRIEND) {
                $repo = $this->entityManager->getRepository(UserRelUser::class);
                // Check if the inverse connection is a friend request.
                $connection = $repo->findOneBy(
                    [
                        'user' => $data->getFriend(),
                        'friend' => $data->getUser(),
                        'relationType' => UserRelUser::USER_RELATION_TYPE_FRIEND,
                    ]
                );

                if ($connection === null) {
                    $connection = (new UserRelUser())
                        ->setUser($data->getFriend())
                        ->setFriend($data->getUser())
                        ->setRelationType(UserRelUser::USER_RELATION_TYPE_FRIEND)
                    ;
                    $this->entityManager->persist($connection);
                    $this->entityManager->flush();
                }
            }
        }

        return $result;
    }
}
