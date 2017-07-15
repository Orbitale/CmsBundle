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

class PageRepository extends AbstractCmsRepository
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
    public function findByCategory(Category $category, $order, $orderBy, $page, $limit): Paginator
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.category = :category')
            ->andWhere('page.enabled = :enabled')
            ->orderBy('page.'.$orderBy, $order)
            ->setMaxResults($limit)
            ->setFirstResult($limit * ($page-1))
            ->setParameter('category', $category)
            ->setParameter('enabled', true)
        ;

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
     * @return Page[]
     */
    public function findFrontPages(array $slugs = [], $host = null, $locale = null)
    {
        $qb = $this->createQueryBuilder('page')
            ->where('page.enabled = :enabled')
            ->leftJoin('page.category', 'category')
            ->andWhere('page.category is null OR category.enabled = :enabled')
            ->setParameter('enabled', true)
        ;

        // Will search differently if we're looking for homepage.
        $searchForHomepage = 0 === count($slugs);

        if (true === $searchForHomepage) {
            // If we are looking for homepage, let's get only the first one.
            $qb
                ->andWhere('page.homepage = :homepage')
                ->setParameter('homepage', true)
                ->setMaxResults(1)
            ;
        } elseif (1 === count($slugs)) {
            $qb
                ->andWhere('page.slug = :slug')
                ->setParameter('slug', reset($slugs))
                ->setMaxResults(1)
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
            $qb->addOrderBy('page.host', 'asc');
        }
        $qb->andWhere($hostWhere);

        $localeWhere = 'page.locale IS NULL';
        if (null !== $locale) {
            $localeWhere .= ' OR page.locale = :locale';
            $qb->setParameter('locale', $locale);
            $qb->addOrderBy('page.locale', 'asc');
        }
        $qb->andWhere($localeWhere);

        // Then the last page will automatically be one that has both properties.
        $qb
            ->orderBy('page.host', 'asc')
            ->addOrderBy('page.locale', 'asc')
        ;

        /** @var Page[] $results */
        $results = $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult()
        ;

        if (0 === count($results)) {
            return $results;
        }

        // If we're looking for a homepage, only get the first result (matching more properties).
        if (true === $searchForHomepage && count($results) > 0) {
            reset($results);
            $results = [$results[0]];
        }

        $resultsSortedBySlug = [];
        foreach ($results as $page) {
            $resultsSortedBySlug[$page->getSlug()] = $page;
        }

        $pages = $resultsSortedBySlug;

        if (count($slugs) > 0) {
            $pages = [];
            foreach ($slugs as $value) {
                if (!array_key_exists($value, $resultsSortedBySlug)) {
                    // Means at least one page in the tree is not enabled
                    return [];
                }

                $pages[$value] = $resultsSortedBySlug[$value];
            }
        }

        return $pages;
    }
}
