<?php
/**
 * Created by PhpStorm.
 * User: Pierstoval
 * Date: 28/03/2015
 * Time: 22:40
 */

namespace Orbitale\Bundle\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractCmsController extends Controller
{

    /**
     * @param array $slugs
     * @param array $elements
     *
     * @return Category|Page
     */
    public function getFinalTreeElement(array $slugs, array $elements)
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