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
     * @param array             $slugs
     * @param Page[]|Category[] $elements
     *
     * @return Category|Page
     */
    protected function getFinalTreeElement(array $slugs, array $elements)
    {
        if (!count($slugs) || count($slugs) !== count($elements)) {
            throw $this->createNotFoundException();
        }

        /** @var Page|Category $element */
        $element = null;

        foreach ($slugs as $k => $slug) {
            $element = null;
            foreach ($elements as $p) {
                if (
                    $p->getSlug() === $slug
                    && $p->isEnabled()
                    && (!$element || $element && $element->getId() === $element->getId())
                ) {
                    $element = $p;
                    break;
                }
            }
            if ($element) {
                continue;
            } else {
                throw $this->createNotFoundException();
            }
        }

        return $element;
    }

}
