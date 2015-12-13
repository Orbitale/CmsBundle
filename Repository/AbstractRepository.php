<?php


namespace Orbitale\Bundle\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AbstractRepository
 * @package Orbitale\Bundle\CmsBundle\Repository
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