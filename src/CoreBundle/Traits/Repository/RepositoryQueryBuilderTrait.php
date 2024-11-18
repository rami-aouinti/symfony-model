<?php

declare(strict_types=1);

namespace App\CoreBundle\Traits\Repository;

/* For licensing terms, see /license.txt */

use Doctrine\ORM\QueryBuilder;

/**
 *
 */
trait RepositoryQueryBuilderTrait
{
    /**
     * @param $alias
     * @param $indexBy
     *
     * @return mixed
     */
    abstract public function createQueryBuilder($alias, $indexBy = null);

    protected function getOrCreateQueryBuilder(QueryBuilder $qb = null, string $alias = 'resource'): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder($alias);
    }
}
