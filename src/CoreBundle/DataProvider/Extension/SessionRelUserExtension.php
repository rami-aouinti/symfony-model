<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Entity\User\User;
use App\Session\Domain\Entity\SessionRelUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;

final class SessionRelUserExtension implements QueryCollectionExtensionInterface // , QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /*public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = []): void
    {
        error_log('applyToItem');
        $this->addWhere($queryBuilder, $resourceClass);
    }*/

    private function addWhere(QueryBuilder $qb, string $resourceClass): void
    {
        if ($resourceClass !== SessionRelUser::class) {
            return;
        }

        $alias = $qb->getRootAliases()[0];

        $qb->innerJoin("{$alias}.session", 's');

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User|null $user */
        if (null === $user = $this->security->getUser()) {
            throw new AccessDeniedException('Access Denied SessionRelUser');
        }

        $qb->andWhere(\sprintf('%s.user = :current_user', $alias));
        $qb->setParameter('current_user', $user->getId());
    }
}
