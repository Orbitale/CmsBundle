<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CmsExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var array
     */
    protected $designConfig;

    public function __construct(array $designConfig)
    {
        $this->designConfig = $designConfig;
    }

    public function getGlobals()
    {
        return [
            'orbitale_cms_design' => $this->designConfig,
        ];
    }
}
