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

use Doctrine\ORM\Tools\Pagination\Paginator;
use Orbitale\Bundle\CmsBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Entity\Page;

class PageRepository extends AbstractRepository
{
    /**
     * @param Category $category
     * @param string   $order
     * @param string   $orderBy
     * @param int      $page
     * @param int      $limit
     *
     * @return Paginator
     */
    public function findByCategory(Category $category, $order, $orderBy, $page, $limit)
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.category = :category')
            ->orderBy('page.'.$orderBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($limit * ($page - 1))
            ->setParameter('category', $category);

        return new Paginator($qb->getQuery()->useResultCache($this->cacheEnabled, $this->cacheTtl));
    }

    /**
     * Will search for pages to show in front depending on the arguments.
     * If slugs are defined, there's no problem in looking for nulled host or locale,
     * because slugs are unique, so it does not.
     *
     * @param array       $slugs
     * @param string|null $host
     * @param string|null $locale
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
        $results = $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();

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
