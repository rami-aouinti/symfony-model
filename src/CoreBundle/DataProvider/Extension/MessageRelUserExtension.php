<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Entity\User\User;
use App\Message\Domain\Entity\MessageRelUser;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class MessageRelUserExtension implements QueryCollectionExtensionInterface
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
        if ($resourceClass !== MessageRelUser::class) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("{$alias}.receiver = :current")
            ->setParameter('current', $user->getId())
        ;
    }
}
