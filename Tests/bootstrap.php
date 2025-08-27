<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

require_once __DIR__.'/Fixtures/App/AppKernel.php';

(static function(){
    $fs = new Filesystem();

    putenv('DATABASE_MAPPING_TYPE=attribute');
    $_SERVER['DATABASE_MAPPING_TYPE'] = 'attribute';
    $_ENV['DATABASE_MAPPING_TYPE'] = 'attribute';

    $kernel = new AppKernel('test', true);

    // Remove build dir files
    if (is_dir($kernel->getBuildDir())) {
        echo "Removing files in the build directory.\n".__DIR__."\n";
        try {
            $fs->remove($kernel->getBuildDir());
        } catch (Exception $e) {
            fwrite(STDERR, $e->getMessage());
        }
    }

    $kernel->boot();

    $databaseFile = $kernel->getContainer()->getParameter('database_path');

    if ($fs->exists($databaseFile)) {
        $fs->remove($databaseFile);
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);
    $out = new ConsoleOutput();
    $application->run(new ArrayInput(['command' => 'doctrine:database:create']), $out);
    $application->run(new ArrayInput(['command' => 'doctrine:schema:update', '--dump-sql' => true, '--force' => true, '--complete' => true]), $out);

    $kernel->shutdown();
})();
