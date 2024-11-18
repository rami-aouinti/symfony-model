<?php

declare(strict_types=1);

namespace App\MyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @package App\MyBundle\DependencyInjection
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('my_bundle');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('some_option')->defaultValue('default_value')->end()
            ->end();

        return $treeBuilder;
    }
}
