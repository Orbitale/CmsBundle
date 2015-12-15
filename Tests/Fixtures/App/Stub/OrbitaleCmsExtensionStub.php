<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures\App\Stub;

use Orbitale\Bundle\CmsBundle\DependencyInjection\OrbitaleCmsExtension;

class OrbitaleCmsExtensionStub extends OrbitaleCmsExtension
{
    /**
     * @var bool
     */
    private $isSymfony3;

    public function __construct($isSymfony3)
    {
        $this->isSymfony3 = $isSymfony3;
    }

    protected function isSymfony3()
    {
        return $this->isSymfony3;
    }
}
