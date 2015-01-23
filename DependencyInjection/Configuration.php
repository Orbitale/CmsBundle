<?php
/*
* This file is part of the PierstovalCmsBundle package.
*
* (c) Alexandre "Pierstoval" Rock Ancelet <pierstoval@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Pierstoval\Bundle\CmsBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('pierstoval_cms');

        $rootNode
            ->children()
                ->scalarNode('site_name')->defaultNull()->end()
                ->arrayNode('layouts')
                    ->defaultValue(array(
                        'front' => array(
                            'resource' => 'PierstovalCmsBundle::default_layout.html.twig',
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
                            ->scalarNode('route')->defaultValue('')->end()
                            ->scalarNode('pattern')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
