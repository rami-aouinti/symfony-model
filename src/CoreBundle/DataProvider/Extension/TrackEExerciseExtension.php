<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Entity\User\User;
use App\Track\Domain\Entity\TrackEExercise;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class TrackEExerciseExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== TrackEExercise::class) {
            return;
        }

        if ($operation->getName() !== 'get') {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedException();
        }

        if ($user->hasRole('ROLE_STUDENT')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq("{$alias}.user", ':user')
            );

            $queryBuilder->setParameter('user', $user->getId());
        }
    }
}
