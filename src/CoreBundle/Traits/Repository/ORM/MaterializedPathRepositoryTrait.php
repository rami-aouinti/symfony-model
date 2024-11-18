<?php

declare(strict_types=1);

namespace App\CoreBundle\Traits\Repository\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Tree\Strategy;

/**
 * The MaterializedPathRepository has some useful functions
 * to interact with MaterializedPath tree. Repository uses
 * the strategy used by listener
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
trait MaterializedPathRepositoryTrait
{
    use TreeRepositoryTrait;

    /**
     * Get tree query builder
     *
     * @param object|null $rootNode
     *
     * @return QueryBuilder
     */
    public function getTreeQueryBuilder(object $rootNode = null): QueryBuilder
    {
        return $this->getChildrenQueryBuilder($rootNode, false, null, 'asc', true);
    }

    /**
     * Get tree query
     *
     * @param object|null $rootNode
     *
     * @return Query
     */
    public function getTreeQuery(object $rootNode = null): Query
    {
        return $this->getTreeQueryBuilder($rootNode)->getQuery();
    }

    /**
     * Get tree
     *
     * @param object|null $rootNode
     *
     * @return array
     */
    public function getTree(object $rootNode = null): array
    {
        return $this->getTreeQuery($rootNode)->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootNodesQueryBuilder($sortByField = null, $direction = 'asc'): QueryBuilder
    {
        return $this->getChildrenQueryBuilder(null, true, $sortByField, $direction);
    }

    /**
     * {@inheritDoc}
     */
    public function getRootNodesQuery($sortByField = null, $direction = 'asc'): Query
    {
        return $this->getRootNodesQueryBuilder($sortByField, $direction)->getQuery();
    }

    /**
     * {}
     */
    public function getRootNodes($sortByField = null, $direction = 'asc')
    {
        return $this->getRootNodesQuery($sortByField, $direction)->execute();
    }

    /**
     * Get the Tree path query builder by given $node
     *
     * @param object $node
     *
     * @return QueryBuilder
     */
    public function getPathQueryBuilder($node): QueryBuilder
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $alias = 'materialized_path_entity';
        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($config['useObjectClass'], $alias);

        $node = new EntityWrapper($node, $this->_em);
        $nodePath = $node->getPropertyValue($config['path']);
        $paths = [];
        $nodePathLength = strlen($nodePath);
        $separatorMatchOffset = 0;
        while ($separatorMatchOffset < $nodePathLength) {
            $separatorPos = strpos($nodePath, $config['path_separator'], $separatorMatchOffset);

            if ($separatorPos === false || $separatorPos === $nodePathLength - 1) {
                // last node, done
                $paths[] = $nodePath;
                $separatorMatchOffset = $nodePathLength;
            } elseif ($separatorPos === 0) {
                // path starts with separator, continue
                $separatorMatchOffset = 1;
            } else {
                // add node
                $paths[] = substr($nodePath, 0, $config['path_ends_with_separator'] ? $separatorPos + 1 : $separatorPos);
                $separatorMatchOffset = $separatorPos + 1;
            }
        }
        $qb->where($qb->expr()->in(
            $alias . '.' . $config['path'],
            $paths
        ));
        $qb->orderBy($alias . '.' . $config['level'], 'ASC');

        return $qb;
    }

    /**
     * Get the Tree path query by given $node
     *
     * @param object $node
     *
     * @return Query
     */
    public function getPathQuery(object $node): Query
    {
        return $this->getPathQueryBuilder($node)->getQuery();
    }

    /**
     * Get the Tree path of Nodes by given $node
     *
     * @param object $node
     *
     * @return array - list of Nodes in path
     */
    public function getPath(object $node): array
    {
        return $this->getPathQuery($node)->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getChildrenQueryBuilder($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false): QueryBuilder
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->getEntityManager(), $meta->name);
        $separator = addcslashes($config['path_separator'], '%');
        $alias = 'materialized_path_entity';
        $path = $config['path'];
        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($config['useObjectClass'], $alias);
        $expr = '';
        $includeNodeExpr = '';

        if (is_object($node) && $node instanceof $meta->name) {
            $node = new EntityWrapper($node, $this->getEntityManager());
            $nodePath = $node->getPropertyValue($path);
            $expr = $qb->expr()->andx()->add(
                $qb->expr()->like(
                    $alias . '.' . $path,
                    $qb->expr()->literal(
                        $nodePath
                        . ($config['path_ends_with_separator'] ? '' : $separator) . '%'
                    )
                )
            );

            if ($includeNode) {
                $includeNodeExpr = $qb->expr()->eq($alias . '.' . $path, $qb->expr()->literal($nodePath));
            } else {
                $expr->add($qb->expr()->neq($alias . '.' . $path, $qb->expr()->literal($nodePath)));
            }

            if ($direct) {
                $expr->add(
                    $qb->expr()->orx(
                        $qb->expr()->eq($alias . '.' . $config['level'], $qb->expr()->literal($node->getPropertyValue($config['level']))),
                        $qb->expr()->eq($alias . '.' . $config['level'], $qb->expr()->literal($node->getPropertyValue($config['level']) + 1))
                    )
                );
            }
        } elseif ($direct) {
            $expr = $qb->expr()->not(
                $qb->expr()->like(
                    $alias . '.' . $path,
                    $qb->expr()->literal(
                        ($config['path_starts_with_separator'] ? $separator : '')
                        . '%' . $separator . '%'
                        . ($config['path_ends_with_separator'] ? $separator : '')
                    )
                )
            );
        }

        if ($expr) {
            $qb->where('(' . $expr . ')');
        }

        if ($includeNodeExpr) {
            $qb->orWhere('(' . $includeNodeExpr . ')');
        }

        $orderByField = $sortByField === null ? $alias . '.' . $config['path'] : $alias . '.' . $sortByField;
        $orderByDir = $direction === 'asc' ? 'asc' : 'desc';
        $qb->orderBy($orderByField, $orderByDir);

        return $qb;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildrenQuery($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false): Query
    {
        return $this->getChildrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode)->getQuery();
    }

    /**
     * {}
     */
    public function getChildren($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false)
    {
        return $this->getChildrenQuery($node, $direct, $sortByField, $direction, $includeNode)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getNodesHierarchyQueryBuilder($node = null, $direct = false, array $options = [], $includeNode = false): QueryBuilder
    {
        $sortBy = [
            'field' => null,
            'dir' => 'asc',
        ];

        if (isset($options['childSort'])) {
            $sortBy = array_merge($sortBy, $options['childSort']);
        }

        return $this->getChildrenQueryBuilder($node, $direct, $sortBy['field'], $sortBy['dir'], $includeNode);
    }

    /**
     * {@inheritdoc}
     */
    public function getNodesHierarchyQuery($node = null, $direct = false, array $options = [], $includeNode = false): Query
    {
        return $this->getNodesHierarchyQueryBuilder($node, $direct, $options, $includeNode)->getQuery();
    }

    /**
     * {}
     */
    public function getNodesHierarchy($node = null, $direct = false, array $options = [], $includeNode = false): array|float|int|string
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $path = $config['path'];

        $nodes = $this->getNodesHierarchyQuery($node, $direct, $options, $includeNode)->getArrayResult();
        usort(
            $nodes,
            function ($a, $b) use ($path) {
                return strcmp($a[$path], $b[$path]);
            }
        );

        return $nodes;
    }

    /**
     * {@inheritdoc}
     */
    protected function validate(): bool
    {
        return $this->listener->getStrategy($this->getEntityManager(), $this->getClassMetadata()->name)->getName() === Strategy::MATERIALIZED_PATH;
    }
}
