<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Entity\User\User;
use App\Track\Domain\Entity\TrackEAttemptQualify;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

use function assert;

/**
 * @package App\CoreBundle\DataProvider\Extension
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class TrackEAttemptQualifyExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== TrackEAttemptQualify::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        assert($user instanceof User);

        $alias = $queryBuilder->getRootAliases()[0];

        // @todo Check permissions with other roles of the current user

        if ($user->isStudent()) {
            $queryBuilder
                ->innerJoin("{$alias}.trackExercise", 'tee')
                ->andWhere('tee.user = :user')
                ->setParameter('user', $user->getId())
            ;
        }
    }
}
