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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PierstovalCmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config['layouts'] as $type => $layout) {
            if (is_numeric($type)) {
                unset($config['layouts'][$type]);
                $type = $layout['type'];
            }
            $config['layouts'][$type] = array_merge(array(
                'type' => $type,
                'assets_css' => '',
                'assets_js' => '',
                'host' => null,
                'route' => '',
                'pattern' => '^/',
            ), $layout);
        }

        $container->setParameter('pierstoval_cms.config', $config);
    }
}
