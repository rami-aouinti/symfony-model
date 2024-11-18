<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Entity\AccessUrlRelCourse;
use App\CoreBundle\Entity\Course\CourseRelUser;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class CourseRelUserExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AccessUrlHelper $accessUrlHelper,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($this->accessUrlHelper->isMultiple()) {
            $accessUrl = $this->accessUrlHelper->getCurrent();
            $rootAlias = $queryBuilder->getRootAliases()[0];
            if (isset($context['filters']['sticky']) && $context['filters']['sticky']) {
                $queryBuilder
                    ->innerJoin(
                        AccessUrlRelCourse::class,
                        'url_rel',
                        'WITH',
                        'url_rel.course = ' . $rootAlias
                    )
                    ->andWhere('url_rel.url = :access_url_id')
                    ->setParameter('access_url_id', $accessUrl->getId())
                ;
            } else {
                $metaData = $this->entityManager->getClassMetadata($resourceClass);
                if ($metaData->hasAssociation('course')) {
                    $queryBuilder
                        ->innerJoin("{$rootAlias}.course", 'c')
                        ->innerJoin('c.urls', 'url_rel')
                        ->andWhere('url_rel.url = :access_url_id')
                        ->setParameter('access_url_id', $accessUrl->getId())
                    ;
                }
            }
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($resourceClass === CourseRelUser::class) {
            if ($operation?->getName() === 'collection_query') {
                /** @var User|null $user */
                if (null === $user = $this->security->getUser()) {
                    throw new AccessDeniedException('Access Denied.');
                }

                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->andWhere(\sprintf('%s.user = :current_user', $rootAlias));
                $queryBuilder->setParameter('current_user', $user->getId());
            }
        }

        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== CourseRelUser::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Need to be login to access the list.
        if (null === $user = $this->security->getUser()) {
            throw new AccessDeniedException('Access Denied.');
        }
    }
}
