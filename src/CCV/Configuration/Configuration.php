<?php
namespace CCV\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ccv');
        $rootNode
        	->children()
        		->arrayNode('database')
                    ->addDefaultsIfNotSet()
        			->children()
        				->scalarNode('driver', 'scalar')->defaultValue('pdo_mysql')->end()
        				->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->defaultValue(3306)->end()
        				->scalarNode('name')->defaultValue('ccv')->end()
                        ->scalarNode('user')->defaultValue('root')->end()
                        ->scalarNode('password')->defaultValue('')->end()
        			->end()
        		->end()
                ->booleanNode('debug')
                    ->defaultFalse()
                ->end()
                ->arrayNode('features')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('bank')->defaultTrue()->end()
                        ->booleanNode('tickets')->defaultTrue()->end()
                    ->end()
                ->end()
                ->booleanNode('log_transactions')
                    ->defaultTrue()
                ->end()
                ->scalarNode('pastebin_id')
                    ->defaultNull()
                ->end()
                ->scalarNode('locale')
                    ->defaultValue('en')
                ->end()
                ->arrayNode('bank')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('initial_balance')
                            ->defaultValue(100)
                        ->end()
                        ->integerNode('daily_amount')
                            ->defaultValue(20)
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('site_name')->defaultValue('CCV')->end()
                ->scalarNode('executable_name')->defaultValue('ccv')->end()
                ->arrayNode('script')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('verbs')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('get')->defaultValue('get')->end()
                                ->scalarNode('put')->defaultValue('put')->end()
                                ->scalarNode('list')->defaultValue('list')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        	->end()
        ;

        return $treeBuilder;
    }
}