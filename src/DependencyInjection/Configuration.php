<?php

namespace Wexample\SymfonyRemoteSparql\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const string WIKIDATA_URL = 'https://query.wikidata.org/sparql';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_remote_sparql');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('user_agent')
                    ->defaultValue('wexample-symfony-remote-sparql/1.0')
                    ->info('Who asks: public endpoints turn away anonymous clients. Name the app.')
                ->end()
                ->arrayNode('endpoints')
                    ->info('The endpoints, by name; Wikidata\'s query service when none is given.')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['wikidata' => [
                        'url' => self::WIKIDATA_URL,
                        'label' => 'Wikidata Query Service',
                        'entity_prefix' => 'http://www.wikidata.org/entity/',
                        'timeout' => 120,
                        'attempts' => 3,
                        'retry_delay' => 10,
                    ]])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('label')->defaultNull()->end()
                            ->scalarNode('entity_prefix')->defaultNull()->info('The URI prefix cut from entity values: `http://www.wikidata.org/entity/` gives `Q42`.')->end()
                            ->floatNode('timeout')->defaultValue(120)->end()
                            ->integerNode('attempts')->defaultValue(3)->min(1)->end()
                            ->integerNode('retry_delay')->defaultValue(10)->min(0)->info('Seconds before the next attempt, times the attempt number.')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
