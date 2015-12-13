<?php

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures\App\Stub;

use Orbitale\Bundle\CmsBundle\DependencyInjection\OrbitaleCmsExtension;

class OrbitaleCmsExtensionStub extends OrbitaleCmsExtension
{
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