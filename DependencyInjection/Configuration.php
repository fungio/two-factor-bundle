<?php

namespace Fungio\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('two_fas_two_factor');

        $supportedDrivers = ['orm', 'custom'];

        $rootNode
            ->children()
                ->scalarNode('account_name')
                    ->isRequired()
                ->end()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
                    ->end()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->cannotBeOverwritten()
                ->end()
                ->scalarNode('encryption_key')
                    ->isRequired()
                ->end()
                ->arrayNode('firewalls')
                    ->isRequired()
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('api_url')->defaultNull()->end()
                ->scalarNode('account_url')->defaultNull()->end()
                ->integerNode('block_user_login_in_minutes')
                    ->min(0)
                    ->max(15)
                    ->defaultValue(5)
                    ->info('Block user for the x minutes after a few failed login attempts.')
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'custom' === $v['db_driver'] && 'two_fas_two_factor.option_persister.default' === $v['persisters']['option_persister'];
                })
                ->thenInvalid('You need to specify your own option persister when using the "custom" driver.')
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'custom' === $v['db_driver'] && 'two_fas_two_factor.user_persister.default' === $v['persisters']['user_persister'];
                })
                ->thenInvalid('You need to specify your own user persister when using the "custom" driver.')
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'custom' === $v['db_driver'] && 'two_fas_two_factor.authentication_persister.default' === $v['persisters']['authentication_persister'];
                })
                ->thenInvalid('You need to specify your own authentication persister when using the "custom" driver.')
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'custom' === $v['db_driver'] && 'two_fas_two_factor.remember_me_persister.default' === $v['persisters']['remember_me_persister'];
                })
                ->thenInvalid('You need to specify your own remember_me persister when using the "custom" driver.')
            ->end();

        $this->addCacheSection($rootNode);
        $this->addEntitiesSection($rootNode);
        $this->addPersistersSection($rootNode);
        $this->addRememberMeSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addEntitiesSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('entities')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('option_class')->defaultNull()->end()
                                ->scalarNode('user_class')->defaultNull()->end()
                                ->scalarNode('authentication_class')->defaultNull()->end()
                                ->scalarNode('remember_me_class')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addPersistersSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('persisters')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('option_persister')->defaultValue('two_fas_two_factor.option_persister.default')->end()
                                ->scalarNode('user_persister')->defaultValue('two_fas_two_factor.user_persister.default')->end()
                                ->scalarNode('authentication_persister')->defaultValue('two_fas_two_factor.authentication_persister.default')->end()
                                ->scalarNode('remember_me_persister')->defaultValue('two_fas_two_factor.remember_me_persister.default')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addRememberMeSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('remember_me')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('lifetime')->defaultValue(31536000)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addCacheSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('cache')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultValue(true)->end()
                                ->variableNode('service')->defaultValue('two_fas_two_factor.storage.file_cache_storage')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
