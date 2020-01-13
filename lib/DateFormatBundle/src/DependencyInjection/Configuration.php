<?php


namespace Blaga\DateFormatBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder The tree builder.
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('blaga_date_format');

        $rootNode
            ->children()
                ->scalarNode('date_format_provider')->defaultNull()->info('Default date format.')->end()
            ->end();

        return $treeBuilder;
    }
}