<?php
/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AbstractTestCase
 * @package Orbitale\Bundle\CmsBundle\Tests\Fixtures
 */
class AbstractTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @param array $options An array of options to pass to the createKernel class
     * @return KernelInterface
     */
    protected function getKernel(array $options = array())
    {
        if (!static::$kernel) {
            $this->setUp();
        }
        return static::$kernel;
    }

    public function setUp()
    {
        if (static::$kernel) {
            static::$kernel->shutdown();
        }
        static::$kernel = static::createKernel(array());
        static::$kernel->boot();
        $kernel = static::getKernel();

        $databaseFile = $kernel->getContainer()->getParameter('database_path');
        $application = new Application($kernel);

        if (file_exists($databaseFile)) {
            unlink($databaseFile);
        }

        // Create database
        $command = new CreateDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:database:create',));
        $command->run($input, new ConsoleOutput());

        // Create database schema
        $command = new CreateSchemaDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:schema:create',));
        $command->run($input, new ConsoleOutput());

        // Check security context, because of deprecation error
        try {
            $this->getKernel()->getContainer()->get('security.context');
        } catch (\Exception $baseException) {
            $e = $baseException;
            do {
                if ($e instanceof \PHPUnit_Framework_Error_Deprecated) {
                    $this->markTestSkipped('Skip deprecated exceptions thrown by Symfony 2.7 until safe release.');
                }
                $e = $e->getPrevious();
            } while ($e);
        }
    }

}
