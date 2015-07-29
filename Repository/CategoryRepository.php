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
use Orbitale\Bundle\CmsBundle\Entity\Category;

class CategoryRepository extends EntityRepository
{
    /**
     * @param array $slugs
     *
     * @return Category[]
     */
    public function findFrontCategories(array $slugs)
    {
        $qb = $this->createQueryBuilder('category')
            ->where('category.enabled = :enabled')
            ->setParameter('enabled', true)
            ->andWhere('category.slug IN ( :slugs )')
            ->setParameter('slugs', $slugs)
        ;

        /** @var Category[] $results */
        $results = $qb->getQuery()->getResult();

        $resultsSortedBySlug = array();

        foreach ($results as $category) {
            $resultsSortedBySlug[$category->getSlug()] = $category;
        }

        return $resultsSortedBySlug;
    }
}
