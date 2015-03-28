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
use Doctrine\ORM\NonUniqueResultException;
use Orbitale\Bundle\CmsBundle\Entity\Page;

class PageRepository extends EntityRepository {

    /**
     * @param null $host
     *
     * @return Page
     * @throws NonUniqueResultException
     */
    public function findHomepage($host = null)
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.homepage = :homepage')
            ->setParameter('homepage', true)
        ;

        $or = $qb->expr()->orX(); // Create an "OR" group
        $or->add($qb->expr()->isNull('page.host')); // Where page.host is null
        if ($host) {
            $or->add($qb->expr()->eq('page.host', ':host'));
            $qb->setParameter('host', $host);
        }
        $qb->andWhere($or);// AND ( page.host is null OR ( page.host is not null and page.host = :host ) )

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param array $criteria
     *
     * @return array The objects.
     */
    public function findCountBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('page')
            ->select('count(page)')
        ;
        foreach ($criteria as $key => $value) {
            $qb
                ->andWhere('page.'.$key.' = :'.$key)
                ->setParameter($key, $value)
            ;
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

}
