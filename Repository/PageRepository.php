<?php

namespace Pierstoval\Bundle\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Pierstoval\Bundle\CmsBundle\Entity\Page;

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
            $and = $qb->expr()->andX();
            $and->add($qb->expr()->isNotNull('page.host')); // page.host is not null
            $and->add($qb->expr()->eq('page.host', ':host')); // and page.host = :host
            $or->add($and);
            $qb->setParameter('host', $host);
        }
        $qb->andWhere($or);// AND ( ... OR ... )

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
