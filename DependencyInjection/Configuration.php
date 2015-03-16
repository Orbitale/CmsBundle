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

/**
 * This is the class that validates and merges configuration from your app/config files
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
                ->arrayNode('layouts')
                    ->defaultValue(array(
                        'front' => array(
                            'resource' => 'OrbitaleCmsBundle::default_layout.html.twig',
                            'pattern' => '^/',
                        ),
                    ))
                    ->useAttributeAsKey('type')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('resource')->isRequired()->end()
                            ->arrayNode('assets_css')->end()
                            ->arrayNode('assets_js')->end()
                            ->scalarNode('route')->end()
                            ->scalarNode('pattern')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
