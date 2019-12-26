<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
* (c) Micael Dias (@aimproxy) <diasmicaelandre@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Controller;


use Exception;
use Orbitale\Bundle\CmsBundle\Entity\Page;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostsController
 * @package Orbitale\Bundle\CmsBundle\Controller
 */
class PostsController extends AbstractCmsController
{
    /**
     * @param Request $request
     * @param string $slugs
     * @param string $year
     * @param string $month
     * @param string $day
     * @param string|null $_locale
     * @return Response
     * @throws Exception
     */
    public function indexAction(Request $request, string $slugs,
                                string $year, string $month,
                                string $day, string $_locale = null): Response
    {
        if (!preg_match('/([12][0-9]{3})/', $year)) {
            throw new Exception('Hups! Year is not the correct format!');
        }

        if (!preg_match('/(0[1-9]|1[012])/', $year)) {
            throw new Exception('Hups! Year is not the correct format!');
        }

        if (!preg_match('/(0[1-9]|[12][0-9]|3[01])/', $day)) {
            throw new Exception('Hups! Day is not the correct format!');
        }

        if (preg_match('#/$#', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_post',
                [
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'slugs' => rtrim($slugs, '/')
                ]
            ));
        }

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        $pages = $this->getPages($slugsArray);

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        return $this->render('@OrbitaleCms/Front/index.html.twig', [
            'pages' => $pages,
            'page' => $currentPage,
        ]);
    }

    /**
     * Retrieves the page list based on slugs.
     * Also checks the hierarchy of the different pages.
     *
     * @param array $slugsArray
     * @return Page[]
     */
    protected function getPages(array $slugsArray = [])
    {
        /** @var Page[] $pages */
        $pages = $this->get('orbitale_cms.page_repository')
            ->findFrontPages($slugsArray, $this->request->getHost(), $this->request->getLocale());

        if (!count($pages) || (count($slugsArray) && count($pages) !== count($slugsArray))) {
            if (count($slugsArray)) {
                throw $this->createNotFoundException('Page not found!');
            }
        }

        return $pages;
    }

    /**
     * Retrieves the current page based on page list and entered slugs.
     *
     * @param array $pages
     * @param array $slugsArray
     * @return Page
     * @throws ReflectionException
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