<?php

namespace FL\XeroBundle\DependencyInjection;

use FL\XeroBundle\XeroPHP\ApplicationFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use XeroPHP\Remote\OAuth\Client;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fl_xero');

        $rootNode
            ->children()
                ->floatNode('core_version')
                    ->info('API versions can be overridden if necessary for some reason.')
                    ->defaultValue('2.0')
                ->end()
                ->floatNode('payroll_version')
                    ->info('API versions can be overridden if necessary for some reason.')
                    ->defaultValue('1.0')
                ->end()
                ->floatNode('file_version')
                    ->info('API versions can be overridden if necessary for some reason.')
                    ->defaultValue('1.0')
                ->end()
                ->arrayNode('oauth')
                    ->children()
                        ->scalarNode('redirect_uri')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_id')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_secret')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('curl')
                    ->info('These are raw curl options, see http://php.net/manual/en/function.curl-setopt.php for details.')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('webhook')
                    ->children()
                        ->scalarNode('signing_key')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
