<?php
/*
* This file is part of the PierstovalCmsBundle package.
*
* (c) Alexandre "Pierstoval" Rock Ancelet <pierstoval@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Pierstoval\Bundle\CmsBundle\Twig;

class CmsExtension extends \Twig_Extension {

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getName()
    {
        return 'pierstoval_cms.twig.extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('cms_layout', array($this, 'getLayout')),
        );
    }

    public function getLayout($layout, $value)
    {
        $layout = '';
    }
}