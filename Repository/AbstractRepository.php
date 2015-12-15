<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This class is used to allow all CmsBundle's repositories to use Doctrine cache.
 * @author Sandor Farkas <farkas.berlin@gmail.com>
 */
class AbstractRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->cacheEnabled = $config['cache']['enabled'];
        $this->cacheTtl = $config['cache']['ttl'];
    }
}
