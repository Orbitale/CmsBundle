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

use Orbitale\Bundle\CmsBundle\DependencyInjection\OrbitaleCmsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;

class OrbitaleCmsExtensionTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Page class must be a valid class extending Orbitale\Bundle\CmsBundle\Entity\Page. "inexistent_page_class" given.
     */
    public function testInexistentPageClass()
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtension();

        $ext->load([
            'orbitale_cms' => [
                'page_class'     => 'inexistent_page_class',
                'category_class' => Category::class,
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

        $ext = new OrbitaleCmsExtension();

        $ext->load([
            'orbitale_cms' => [
                'page_class'     => Page::class,
                'category_class' => 'inexistent_category_class',
            ],
        ], $builder);
    }

    /**
     * @dataProvider provideYamlConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testYamlConfiguration($config, $expected)
    {
        $builder = new ContainerBuilder();

        $ext = new OrbitaleCmsExtension();

        $ext->load($config, $builder);

        static::assertArrayHasKey('orbitale_cms', $expected);

        foreach ($expected['orbitale_cms'] as $key => $expectedValue) {
            static::assertEquals($expectedValue, $builder->getParameter('orbitale_cms.'.$key));
        }
    }

    public function provideYamlConfiguration(): array
    {
        $dir = __DIR__.'/../Fixtures/App/extension_test/';

        $configFiles = glob($dir.'config_*.yaml');
        $resultFiles = glob($dir.'result_*.yaml');

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
