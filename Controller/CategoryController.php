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

use Doctrine\ORM\EntityManager;
use Orbitale\Bundle\CmsBundle\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractCmsController
{
    /**
     * @Route("/{slugs}", name="orbitale_cms_category", requirements={"slugs": "([a-zA-Z0-9_-]+\/?)+"})
     */
    public function indexAction($slugs = '', Request $request)
    {
        if (preg_match('#/$#', $slugs)) {
            return $this->redirect($this->generateUrl('orbitale_cms_category', array('slugs' => rtrim($slugs, '/'))));
        }

        $slugsArray = preg_split('~/~', $slugs, -1, PREG_SPLIT_NO_EMPTY);



        $categories = $this->get('orbitale_cms.category_repository')->findFrontCategories($slugsArray);

        $category = $this->getFinalTreeElement($slugsArray, $categories);

        $validOrderFields = array('createdAt', 'updatedAt', 'title', 'content');

        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);
        $orderBy = $request->query->get('order_by', current($validOrderFields));
        $order = $request->query->get('order', 'asc');

        $pages = $this->get('orbitale_cms.page_repository')->findByCategory(
            $category,
            $order,
            $orderBy,
            $page,
            $limit
        );

        return $this->render('OrbitaleCmsBundle:Front:category.html.twig', array(
            'category' => $category,
            'categories' => $categories,
            'pages' => $pages,
            'pagesCount' => count($pages),
            'filters' => array(
                'page' => $page,
                'limit' => $limit,
                'orderBy' => $orderBy,
                'order' => $order,
            ),
        ));
    }
}
