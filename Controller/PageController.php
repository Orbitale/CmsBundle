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
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractCmsController
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @Route("/{slugs}", name="orbitale_cms_page", requirements={"slugs": "([a-zA-Z0-9_-]+\/?)*"}, defaults={"slugs": ""})
     *
     * @param Request     $request
     * @param string      $slugs
     * @param string|null $_locale
     *
     * @return Response
     */
    public function indexAction(Request $request, $slugs = '', $_locale = null)
    {
        if (preg_match('~/$~', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_page', array('slugs' => rtrim($slugs, '/'))));
        }

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        $pages = $this->getPages($slugsArray);

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        if ($currentPage->isHomepage() && strlen($slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_page', array('slugs' => '', '_locale' => $this->request->getLocale())));
        }

        return $currentPage instanceof Response
            ? $currentPage
            : $this->render('OrbitaleCmsBundle:Front:index.html.twig', array(
                'pages' => $pages,
                'page' => $currentPage,
            ))
        ;
    }

    /**
     * Retrieves the page list based on slugs.
     * Also checks the hierarchy of the different pages.
     *
     * @param array $slugsArray
     *
     * @return Page[]
     */
    protected function getPages(array $slugsArray = array())
    {
        /** @var Page[] $pages */
        $pages = $this->getDoctrine()->getManager()
            ->getRepository('OrbitaleCmsBundle:Page')
            ->findFrontPages($slugsArray, $this->request->getHost(), $this->request->getLocale())
        ;

        if (!count($pages) || (count($slugsArray) && count($pages) !== count($slugsArray))) {
            if (count($slugsArray)) {
                $msg = 'Page not found';
            } else {
                $msg = 'No homepage has been configured. Please check your existing pages or create a homepage in your application.';
            }
            throw $this->createNotFoundException($msg);
        }

        return $pages;
    }

    /**
     * Retrieves the current page based on page list and entered slugs
     *
     * @param Page[] $pages
     * @param array  $slugsArray
     *
     * @return Page
     */
    protected function getCurrentPage(array $pages, array $slugsArray)
    {
        if (count($pages) === count($slugsArray)) {
            $currentPage = $this->getFinalTreeElement($slugsArray, $pages);
        } else {
            $currentPage = current($pages);
        }

        return $currentPage;
    }
}
