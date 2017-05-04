<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Controller;

use Orbitale\Bundle\CmsBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractCmsController extends Controller
{
    /**
     * Slugs HAVE TO be ordered exactly as in the request.
     * This method will check that, in $elements, we have the same keys as in $slugs,
     * and that the hierarchy is correct.
     * This also prevents things like /children/parent to work,
     * as it should be /parent/children.
     *
     * @param array             $slugs
     * @param Page[]|Category[] $elements
     *
     * @return Category|Page
     */
    protected function getFinalTreeElement(array $slugs, array $elements)
    {
        // Will check that slugs and elements match
        $slugsElements = array_keys($elements);
        $sortedSlugs   = $slugs;
        sort($sortedSlugs);
        sort($slugsElements);

        if ($sortedSlugs !== $slugsElements || !count($slugs) || count($slugs) !== count($elements)) {
            throw $this->createNotFoundException();
        }

        /** @var Page|Category $element */
        $element = null;
        /** @var Page|Category $previousElement */
        $previousElement = null;

        foreach ($slugs as $slug) {
            $element = $elements[$slug] ?? null;
            $match   = false;
            if ($element) {
                // Only for the first iteration
                $match = $previousElement
                    ? $element->getParent() && $previousElement->getSlug() === $element->getParent()->getSlug()
                    : true;

                $previousElement = $element;
            }
            if (!$match) {
                throw $this->createNotFoundException((new \ReflectionClass($element))->getShortName().' hierarchy not found.');
            }
        }

        return $element;
    }
}
