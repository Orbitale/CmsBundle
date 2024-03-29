<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures\App;

use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new \Orbitale\Bundle\CmsBundle\OrbitaleCmsBundle(),
            new \Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\TestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yaml');

        $loader->load(static function (ContainerBuilder $container) {
            $container->register('logger', NullLogger::class);
        });
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../../../build/cache/'.$this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../../../build/kernel_logs/'.$this->getEnvironment();
    }
}
