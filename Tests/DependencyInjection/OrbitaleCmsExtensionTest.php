<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\DependencyInjection;

use Orbitale\Bundle\CmsBundle\Tests\Fixtures\App\Stub\OrbitaleCmsExtensionStub;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class OrbitaleCmsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Page class must be a valid class extending Orbitale\Bundle\CmsBundle\Entity\Page. "inexistent_page_class" given.
     */
    public function testInexistentPageClass()
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtensionStub(true);

        $ext->load([
            'orbitale_cms' => [
                'page_class'     => 'inexistent_page_class',
                'category_class' => 'Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Category',
            ],
        ], $builder);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Category class must be a valid class extending Orbitale\Bundle\CmsBundle\Entity\Category. "inexistent_category_class" given.
     */
    public function testInexistentCategoryClass()
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtensionStub(true);

        $ext->load([
            'orbitale_cms' => [
                'page_class'     => 'Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page',
                'category_class' => 'inexistent_category_class',
            ],
        ], $builder);
    }

    /**
     * @dataProvider provideConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testConfiguration($config, $expected)
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtensionStub(true);

        $ext->load($config, $builder);

        foreach ($expected['orbitale_cms'] as $key => $value) {
            static::assertSame($value, $builder->getParameter('orbitale_cms.' . $key));
        }
    }

    /**
     * @dataProvider provideConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testConfigurationSymfony2($config, $expected)
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtensionStub(false);

        $ext->load($config, $builder);

        foreach ($expected['orbitale_cms'] as $key => $value) {
            static::assertSame($value, $builder->getParameter('orbitale_cms.' . $key));
        }
    }

    public function provideConfiguration()
    {
        $dir = __DIR__ . '/../Fixtures/App/extension_test/';

        $configFiles = glob($dir . 'config_*.yml');
        $resultFiles = glob($dir . 'result_*.yml');

        sort($configFiles);
        sort($resultFiles);

        $tests = [];

        foreach ($configFiles as $k => $file) {
            $tests[] = [
                Yaml::parse(file_get_contents($file)),
                Yaml::parse(file_get_contents($resultFiles[$k])),
            ];
        }

        return $tests;
    }
}
