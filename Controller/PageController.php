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

use Orbitale\Bundle\CmsBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractCmsController
{
    /**
     * @Route("/{slugs}", name="orbitale_cms_page", requirements={"slugs": "([a-zA-Z0-9_-]+\/?)*"}, defaults={"slugs": ""})
     *
     * @param string  $slugs
     * @param string  $_locale
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($slugs = '', $_locale = null, Request $request)
    {
        if (preg_match('~/$~', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_page', array('slugs' => rtrim($slugs, '/'))));
        }

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        /** @var Page[] $pages */
        $pages = $this->getDoctrine()->getManager()
            ->getRepository('OrbitaleCmsBundle:Page')
            ->findFrontPages($slugsArray, $request->getHost(), $_locale ?: $request->getLocale())
        ;

        if (!count($pages) || (count($slugsArray) && count($pages) !== count($slugsArray))) {
            if (count($slugsArray)) {
                $msg = 'Page not found';
            } else {
                $msg = 'No homepage has been configured. Please check your existing pages or create a homepage in your application.';
            }
            throw $this->createNotFoundException($msg);
        }

        if (count($pages) === count($slugsArray)) {
            /** @var Page $currentPage */
            $currentPage = $this->getFinalTreeElement($slugsArray, $pages);
        } else {
            $currentPage = current($pages);
        }

        if ($currentPage->isHomepage() && strlen($slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_page', array('slugs' => '', '_locale' => $_locale)));
        }

        return $this->render('OrbitaleCmsBundle:Front:index.html.twig', array(
            'pages' => $pages,
            'page' => $currentPage,
        ));
    }
}
