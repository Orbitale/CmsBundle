<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity;

use Orbitale\Bundle\CmsBundle\Entity\Page as BasePage;
use Doctrine\ORM\Mapping as ORM;
use Orbitale\Bundle\CmsBundle\Repository\PageRepository;

class Page extends BasePage
{
    /**
     * @var int
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}
