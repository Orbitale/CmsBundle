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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;

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
        static::bootKernel($options);
        return static::$kernel;
    }

    public function setUp()
    {
        self::$kernel = static::createKernel();

        self::$kernel->boot();
        self::$container = self::$kernel->getContainer();

        $application = new Application(self::getKernel());

        // Drop the database
        $command = new DropDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:database:drop','--force' => true,));
        $command->run($input, new ConsoleOutput());

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
    }
}
