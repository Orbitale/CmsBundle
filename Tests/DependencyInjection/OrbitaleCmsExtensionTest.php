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

        $this->assertEquals($expected['orbitale_cms'], $builder->getParameter('orbitale_cms.config'));
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

        $this->assertEquals($expected['orbitale_cms'], $builder->getParameter('orbitale_cms.config'));
    }

    public function provideConfiguration()
    {
        $dir = __DIR__.'/../Fixtures/App/extension_test/';

        $configFiles = glob($dir.'config_*.yml');
        $resultFiles = glob($dir.'result_*.yml');

        sort($configFiles);
        sort($resultFiles);

        $tests = array();

        foreach ($configFiles as $k => $file) {
            $tests[] = array(
                Yaml::parse(file_get_contents($file)), Yaml::parse(file_get_contents($resultFiles[$k])),
            );
        }

        return $tests;
    }
}
