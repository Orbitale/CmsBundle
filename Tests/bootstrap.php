<?php

declare(strict_types=1);

/*
 * This file is part of the OrbitaleCmsBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Orbitale\Bundle\CmsBundle\Tests\Fixtures\App\AppKernel;
use Symfony\Component\Filesystem\Filesystem;

$file = __DIR__.'/../vendor/autoload.php';
if (!\file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

require_once __DIR__.'/Fixtures/App/AppKernel.php';

(static function (): void {
    $fs = new Filesystem();

    $kernel = new AppKernel('test', true);

    // Remove build dir files
    if (\is_dir($kernel->getBuildDir())) {
        echo "Removing files in the build directory.\n".__DIR__."\n";

        try {
            $fs->remove($kernel->getBuildDir());
        } catch (Exception $e) {
            \fwrite(\STDERR, $e->getMessage());
        }
    }
    unset($kernel);
})();
