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
use Orbitale\Bundle\CmsBundle\Entity\Page;
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

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param Request $request
     * @param string $slugs
     * @param string $date
     * @return Response
     */
    public function indexAction(Request $request, string $slugs = '',
                                string $date = '', string $_date_format = null,
                                string $_locale = null): Response
    {
        if (!$this->isValidDate($date, $_date_format)) {
            throw $this->createNotFoundException("Invalid Date format provided");
        }

        if (preg_match('#/$#', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_post',
                [
                    'date' => $date,
                    'slugs' => rtrim($slugs, '/')
                ]
            ));
        }

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        if (!$slugsArray) {
            throw $this->createNotFoundException("Slug not found");
        }

        $pages = $this->pageRepository
            ->findFrontPages($slugsArray, $this->request->getHost(), $this->request->getLocale());

        if (!count($pages) || (count($slugsArray) && count($pages) !== count($slugsArray))) {
            throw $this->createNotFoundException("Post not found");
        }

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        return $this->render('@OrbitaleCms/Front/index.html.twig', [
            'pages' => $pages,
            'page' => $currentPage,
        ]);
    }

    /**
     * @param $date
     * @param $format
     * @return bool
     */
    function isValidDate($date, $format): bool
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    /**
     * @param array $pages
     * @param array $slugsArray
     * @return Page
     */
    public function getCurrentPage(array $pages, array $slugsArray): Page
    {
        if (count($pages) === count($slugsArray)) {
            return $this->getFinalTreeElement($slugsArray, $pages);
        } else {
            $currentPage = current($pages);
        }

        return $currentPage;
    }
}
