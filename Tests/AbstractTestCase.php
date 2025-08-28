<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests;

use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class AbstractTestCase extends WebTestCase
{
    public function setUp(): void
    {
        self::installDatabase();
    }

    public static function installDatabase(): void
    {
        static::bootKernel();

        $databaseFile = self::$kernel->getContainer()->getParameter('database_path');

        $fs = new Filesystem();

        if ($fs->exists($databaseFile)) {
            $fs->remove($databaseFile);
        }

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $out = new BufferedOutput();
        $returns = [];
        $returns[] = $application->run(new ArrayInput(['command' => 'doctrine:database:create']), $out);
        $returns[] = $application->run(new ArrayInput(['command' => 'doctrine:schema:update', '--dump-sql' => true, '--complete' => true]), $out);
        $returns[] = $application->run(new ArrayInput(['command' => 'doctrine:schema:create']), $out);

        if (\in_array(1, $returns, true)) {
            self::fail(\sprintf("A database setup command has failed:\n%s", $out->fetch()));
        }

        static::ensureKernelShutdown();
    }

    protected function createPage(array $values = []): Page
    {
        $page = new Page();

        $set = \Closure::bind(function(string $property, $value) {
            if (!property_exists(Page::class, $property)){
                throw new \InvalidArgumentException(sprintf("Property %s does not exist in %s", $property, Page::class));
            }
            $this->{$property} = $value;
        }, $page, Page::class);

        foreach ($values as $key => $value) {
            $set($key, $value);
        }

        return $page;
    }
}
