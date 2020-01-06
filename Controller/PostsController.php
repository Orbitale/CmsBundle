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

use DateTime;
use Exception;
use Orbitale\Bundle\CmsBundle\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostsController
 * @package Orbitale\Bundle\CmsBundle\Controller
 */
class PostsController extends AbstractCmsController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageController
     */
    private $pageController;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageController = new PageController($pageRepository);
    }

    /**
     * @param Request $request
     * @param string $slugs
     * @param string $year
     * @param string $month
     * @param string $day
     * @return Response
     * @throws Exception
     */
    public function indexAction(Request $request, string $slugs,
                                string $year, string $month,
                                string $day): Response
    {
        if ($this->validateDate("$year-$month-$day") != true) {
            throw $this->createNotFoundException();
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

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);
        $pages = $this->pageController->getPages($slugsArray);
        $currentPage = $this->pageController->getCurrentPage($pages, $slugsArray);

        return $this->render('@OrbitaleCms/Front/index.html.twig', [
            'pages' => $pages,
            'page' => $currentPage,
        ]);
    }

    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}