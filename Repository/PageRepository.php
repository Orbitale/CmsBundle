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
use Orbitale\Bundle\CmsBundle\Entity\Page;

class PageRepository extends EntityRepository
{

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

    /**
     * Will search for pages to show in front depending on the arguments.
     * If slugs are defined, there's no problem in looking for nulled host or locale,
     * because slugs are unique, so it does not
     *
     * @param array $slugs
     * @param string $host
     * @param string $locale
     *
     * @return Page
     */
    public function findFrontPages(array $slugs = array(), $host = null, $locale = null)
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.enabled = :enabled')
            ->leftJoin('page.category', 'category')
            ->andWhere('page.category is null OR category.enabled = :enabled')
            ->setParameter('enabled', true)
        ;

        // Will search differently if we're looking for homepage.
        $searchForHomepage = count($slugs) === 0;

        if ($searchForHomepage) {
            $qb
                ->andWhere('page.homepage = :homepage')
                ->setParameter('homepage', true)
            ;
        } else {
            $qb
                ->andWhere('page.slug IN ( :slugs )')
                ->setParameter('slugs', $slugs)
            ;
        }

        $hostWhere = 'page.host IS NULL';
        if (null !== $host) {
            $hostWhere .= ' OR page.host = :host';
            $qb->setParameter('host', $host);
        }
        $qb->andWhere($hostWhere);

        $localeWhere = 'page.locale IS NULL';
        if (null !== $locale) {
            $localeWhere .= ' OR page.locale = :locale';
            $qb->setParameter('locale', $locale);
        }
        $qb->andWhere($localeWhere);

        // This will allow getting first the pages that match both criteria
        $qb
            ->orderBy('page.host', 'DESC')
            ->addOrderBy('page.locale', 'DESC')
        ;

        /** @var Page[] $results */
        $results = $qb->getQuery()->getResult();

        if ($searchForHomepage) {
            $homepage = null;

            foreach ($results as $page) {
                if (
                    ($page->getLocale() && $page->getHost())
                    || $page->getHost() || $page->getLocale()
                    || !$page->getLocale() || !$page->getHost()
                ) {
                    $homepage = $page;
                    break;
                }
            }

            $results = $homepage ? array($homepage) : array();
        }

        $resultsSortedBySlug = array();

        foreach ($results as $page) {
            $resultsSortedBySlug[$page->getSlug()] = $page;
        }

        return $resultsSortedBySlug;
    }

}
