<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

if (is_dir(__DIR__.'/../build')) {
    echo "Removing files in the build directory.\n".__DIR__."\n";
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../build/', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
}

AnnotationRegistry::registerLoader(function($class) use ($autoload) {
    $autoload->loadClass($class);
    return class_exists($class, false);
});


include __DIR__.'/Fixtures/App/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

$databaseFile = $kernel->getContainer()->getParameter('database_path');
$application = new Application($kernel);

if (file_exists($databaseFile)) {
    unlink($databaseFile);
}

// Create database
$command = new CreateDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array('command' => 'doctrine:database:create'));
$command->run($input, new NullOutput());

// Create database schema
$command = new CreateSchemaDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array('command' => 'doctrine:schema:create'));
$command->run($input, new NullOutput());

$kernel->shutdown();
