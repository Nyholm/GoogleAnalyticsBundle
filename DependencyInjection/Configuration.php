<?php

namespace Happyr\Google\AnalyticsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('happyr_google_analytics');

        $rootNode
            ->children()
            ->scalarNode('version')->cannotBeEmpty()->defaultValue(1)->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->scalarNode('tracking_id')->isRequired()->cannotBeEmpty()->end()
            ->integerNode('profile_id')->defaultNull()->info('The google analytics profile id. This is not the same as the tracking code.')->end()
            ->scalarNode('endpoint')->defaultValue('http://www.google-analytics.com/collect')->cannotBeEmpty()->end()
            ->booleanNode('fireAndForget')->defaultFalse()->end()
            ->floatNode('requestTimeout')->defaultValue(1)->end()
            ->floatNode('cache_service')->defaultNull()->end()
            ->end()->end();

        return $treeBuilder;
    }
}
