<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('orbitale_cms');

        $rootNode
            ->children()
                ->scalarNode('page_class')
                    ->isRequired()
                    ->validate()
                        ->ifString()
                        ->then(function ($value) {
                            if (!class_exists($value) || !is_a($value, 'Orbitale\Bundle\CmsBundle\Entity\Page', true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Page class must be a valid class extending %s. "%s" given.',
                                    'Orbitale\Bundle\CmsBundle\Entity\Page', $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->scalarNode('category_class')
                    ->isRequired()
                    ->validate()
                        ->ifString()
                        ->then(function ($value) {
                            if (!class_exists($value) || !is_a($value, 'Orbitale\Bundle\CmsBundle\Entity\Category', true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Category class must be a valid class extending %s. "%s" given.',
                                    'Orbitale\Bundle\CmsBundle\Entity\Category', $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->arrayNode('layouts')
                    ->defaultValue(array(
                        'front' => array(
                            'resource' => 'OrbitaleCmsBundle::default_layout.html.twig',
                            'pattern' => '^/',
                        ),
                    ))
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('resource')->isRequired()->end()
                            ->arrayNode('assets_css')->end()
                            ->arrayNode('assets_js')->end()
                            ->scalarNode('pattern')->end()
                            ->scalarNode('host')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('design')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('breadcrumbs_class')->defaultValue('breadcrumb')->end()
                        ->scalarNode('breadcrumbs_link_class')->defaultValue('')->end()
                        ->scalarNode('breadcrumbs_current_class')->defaultValue('')->end()
                        ->scalarNode('breadcrumbs_separator')->defaultValue('>')->end()
                        ->scalarNode('breadcrumbs_separator_class')->defaultValue('breadcrumb-separator')->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->integerNode('ttl')->defaultValue(300)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
