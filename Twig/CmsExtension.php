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

class CmsExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $cmsConfig;

    public function __construct(array $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
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
        return array(
            'orbitale_cms_config' => $this->cmsConfig,
        );
    }
}
