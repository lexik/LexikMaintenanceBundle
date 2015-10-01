<?php

namespace Lexik\Bundle\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lexik_maintenance');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('authorized')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('host')
                            ->defaultNull()
                        ->end()
                        ->variableNode('ips')
                            ->defaultNull()
                        ->end()
                        ->variableNode('query')
                            ->defaultValue(array())
                        ->end()
                        ->variableNode('cookie')
                            ->defaultValue(array())
                        ->end()
                        ->scalarNode('route')
                            ->defaultNull()
                        ->end()
                        ->variableNode('attributes')
                            ->defaultValue(array())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('driver')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultNull()
                        ->end()
                        ->integerNode('ttl')
                            ->defaultNull()
                        ->end()
                        ->variableNode('options')
                            ->defaultValue(array())
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('response')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('code')
                            ->defaultValue( 503 )
                        ->end()
                        ->scalarNode('status')
                            ->defaultValue( "Service Temporarily Unavailable")
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
