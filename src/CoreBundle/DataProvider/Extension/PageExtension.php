<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataProvider\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\CoreBundle\Component\Utils\CreateDefaultPages;
use App\CoreBundle\Entity\Page;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class PageExtension implements QueryCollectionExtensionInterface // , QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AccessUrlHelper $accessUrlHelper,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Page::class) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $url = $this->accessUrlHelper->getCurrent();

        // Url filter by default.
        $queryBuilder
            ->andWhere("{$alias}.url = :url")
            ->setParameter('url', $url->getId())
            ->innerJoin(
                "{$alias}.category",
                'category',
            )
        ;

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $queryBuilder->andWhere("{$alias}.enabled = 1")
                ->andWhere(
                    $queryBuilder->expr()->notIn(
                        'category.title',
                        CreateDefaultPages::getCategoriesForAdminBlocks()
                    )
                )
            ;
        }

        if (!$this->security->isGranted('IS_AUTHENTICATED')) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->in('category.title', ':anon_categories')
                )
                ->setParameter(
                    'anon_categories',
                    [
                        'faq',
                        'demo',
                        'home',
                        'public',
                        'footer_public',
                    ]
                )
            ;
        }
    }
}
