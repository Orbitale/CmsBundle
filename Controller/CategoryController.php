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

use Orbitale\Bundle\CmsBundle\Repository\CategoryRepository;
use Orbitale\Bundle\CmsBundle\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractCmsController
{
    private $categoryRepository;
    private $pageRepository;

    public function __construct(CategoryRepository $categoryRepository, PageRepository $pageRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param string  $slugs
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request, string $slugs = ''): Response
    {
        if (preg_match('#/$#', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_category', ['slugs' => rtrim($slugs, '/')]));
        }

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);

        $categories = $this->categoryRepository->findFrontCategories($slugsArray);

        $category = $this->getFinalTreeElement($slugsArray, $categories);

        $validOrderFields = ['createdAt', 'id', 'title', 'content'];

        $limit   = $request->query->get('limit', 10);
        $page    = $request->query->get('page', 1);
        $order   = $request->query->get('order', 'asc');
        $orderBy = $request->query->get('order_by', current($validOrderFields));
        if (!in_array($orderBy, $validOrderFields, true)) {
            $orderBy = current($validOrderFields);
        }

        $pages = $this->pageRepository->findByCategory(
            $category,
            $order,
            $orderBy,
            $page,
            $limit
        );

        return $this->render('@OrbitaleCms/Front/category.html.twig', [
            'category'   => $category,
            'categories' => $categories,
            'pages'      => $pages,
            'pagesCount' => count($pages),
            'filters'    => [
                'page'    => $page,
                'limit'   => $limit,
                'orderBy' => $orderBy,
                'order'   => $order,
            ],
        ]);
    }
}
