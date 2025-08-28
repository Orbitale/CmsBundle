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

namespace Orbitale\Bundle\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This class is used to allow all CmsBundle's repositories to use Doctrine cache.
 *
 * @author Sandor Farkas <farkas.berlin@gmail.com>
 */
class AbstractCmsRepository extends EntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    public function setConfig(array $cacheConfig): void
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }
}
