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

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require_once $file;

if (is_dir(__DIR__.'/../build')) {
    echo "Removing files in the build directory.\n";
    exec('rm -rf '.__DIR__.'/../build/cache/');
    exec('rm -rf '.__DIR__.'/../build/test.db');
}

AnnotationRegistry::registerLoader(function($class) use ($autoload) {
    $autoload->loadClass($class);
    return class_exists($class, false);
});
