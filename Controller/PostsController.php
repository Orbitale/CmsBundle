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

class PostsController extends AbstractCmsController
{
    /**
     * @var Request
     */
    private $request;

    private $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function indexAction(Request $request, string $slugs = '', string $date = '', string $_date_format = null, string $_locale = null): Response
    {
        if (!$this->isValidDate($date, $_date_format)) {
            throw $this->createNotFoundException("Invalid date format provided");
        }

        if (!$slugs || '/' === $slugs) {
            throw $this->createNotFoundException("No page identifier provided");
        }

        $this->request = $request;
        $this->request->setLocale($_locale ?: $this->request->getLocale());

        $slugsArray = \preg_split('~/~', $slugs, -1, \PREG_SPLIT_NO_EMPTY);

        $pages = $this->pageRepository
            ->findFrontPages($slugsArray, $this->request->getHost(), $this->request->getLocale())
        ;

        $numberOfSlugs = \count($slugsArray);
        $numberOfPages = \count($pages);
        if (!$numberOfPages || ($numberOfSlugs && $numberOfPages !== $numberOfSlugs)) {
            throw $this->createNotFoundException("Post not found");
        }

        $currentPage = $this->getCurrentPage($pages, $slugsArray);

        if ($currentPage->getCreatedAt()->format($_date_format) !== $date) {
            throw $this->createNotFoundException('Date in URL does not match post\'s date.');
        }

        return $this->render('@OrbitaleCms/Front/index.html.twig', [
            'pages' => $pages,
            'page' => $currentPage,
        ]);
    }

    function isValidDate(string $date, string $format): bool
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    public function getCurrentPage(array $pages, array $slugsArray): Page
    {
        if (count($pages) === count($slugsArray)) {
            return $this->getFinalTreeElement($slugsArray, $pages);
        }

        return \current($pages);
    }
}
