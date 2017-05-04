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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends AbstractCmsController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Request     $request
     * @param string      $slugs
     * @param string|null $_locale
     *
     * @return Response
     */
    public function indexAction(Request $request, string $slugs = '', string $_locale = null): Response
    {
        if (preg_match('~/$~', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_page', ['slugs' => rtrim($slugs, '/')]));
        }

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        $pages = $this->getPages($slugsArray);

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        // If we have slugs and the current page is homepage,
        //  we redirect to homepage for "better" url and SEO management.
        // Example: if "/home" is a homepage, "/home" url is redirected to "/".
        if ($slugs && $currentPage->isHomepage()) {
            $params = ['slugs' => ''];
            if ($currentPage->getLocale()) {
                // Force locale if the Page has one
                $params['_locale'] = $currentPage->getLocale();
            }

            return $this->redirect($this->generateUrl('orbitale_cms_page', $params));
        }

        return $this->render('@OrbitaleCms/Front/index.html.twig', [
            'pages' => $pages,
            'page'  => $currentPage,
        ]);
    }

    /**
     * Retrieves the page list based on slugs.
     * Also checks the hierarchy of the different pages.
     *
     * @param array $slugsArray
     *
     * @return Page[]
     */
    protected function getPages(array $slugsArray = [])
    {
        /** @var Page[] $pages */
        $pages = $this->get('orbitale_cms.page_repository')
            ->findFrontPages($slugsArray, $this->request->getHost(), $this->request->getLocale())
        ;

        if (!count($pages) || (count($slugsArray) && count($pages) !== count($slugsArray))) {
            throw $this->createNotFoundException(count($slugsArray)
                ? 'Page not found'
                : 'No homepage has been configured. Please check your existing pages or create a homepage in your application.');
        }

        return $pages;
    }

    /**
     * Retrieves the current page based on page list and entered slugs.
     *
     * @param Page[] $pages
     * @param array  $slugsArray
     *
     * @return Page
     */
    protected function getCurrentPage(array $pages, array $slugsArray): Page
    {
        if (count($pages) === count($slugsArray)) {
            $currentPage = $this->getFinalTreeElement($slugsArray, $pages);
        } else {
            $currentPage = current($pages);
        }

        return $currentPage;
    }
}
