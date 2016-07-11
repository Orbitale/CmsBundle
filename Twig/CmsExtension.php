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

class CmsExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var array
     */
    protected $designConfig;

    public function __construct(array $designConfig)
    {
        $this->designConfig = $designConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orbitale_cms_twig_extension';
    }

    public function getGlobals()
    {
        return [
            'orbitale_cms_design' => $this->designConfig,
        ];
    }
}
