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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OrbitaleCmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['layouts'] as $name => $layout) {
            $config['layouts'][$name] = array_merge(array(
                'name' => $name,
                'assets_css' => '',
                'assets_js' => '',
                'host' => '',
                'pattern' => '',
            ), $layout);
            if (!$config['layouts'][$name]['host'] && !$config['layouts'][$name]['pattern']) {
                // If the user does not specify anything in the host nor the pattern,
                //  we force the pattern to match at least the root, else the layout would never be used...
                $config['layouts'][$name]['pattern'] = '^/';
            }
        }

        // Sort configs by host, because host is checked before pattern.
        uasort($config['layouts'], function($a, $b) {
            if ($a['host'] && $b['host']) {
                return strcasecmp($a['host'], $b['host']);
            } elseif ($a['host'] && !$b['host']) {
                return -1;
            } else {
                return 1;
            }
        });

        $container->setParameter('orbitale_cms.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
