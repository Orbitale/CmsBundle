<?php

namespace Pierstoval\Bundle\CmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pierstoval\Bundle\CmsBundle\Entity\Page;

class PageRepository extends EntityRepository {

    /**
     * @param null $host
     *
     * @return Page
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findHomepage($host = null)
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.homepage = :homepage')
            ->setParameter('homepage', true)
        ;
        if ($host) {
            $qb->andWhere('page.host = :host')->setParameter('host', $host);
        } else {
            $qb->andWhere('page.host is null');
        }
        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
