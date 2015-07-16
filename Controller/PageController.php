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
use Orbitale\Bundle\CmsBundle\Repository\PageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractCmsController
{

    /**
     * @Route("/{slugs}", name="orbitale_cms_page", requirements={"slugs": "([a-zA-Z0-9_-]+\/?)*"}, defaults={"slugs": ""})
     */
    public function indexAction($slugs = '', $_locale = null, Request $request)
    {
        if (!$slugs) {
            $slugs = $this->getHomepage($request->getHost());
        }

        $slugsArray = explode('/', $slugs);

        /** @var PageRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository('OrbitaleCmsBundle:Page');

        $params = array(
            'locale' => $request->getLocale(),
        );

        /** @var Page[] $pages */
        $pages = $repo->findFrontPage($slugsArray, $params);

        return $this->render('OrbitaleCmsBundle:Front:index.html.twig', array(
            'pages' => $pages,
            'page'  => $this->getFinalTreeElement($slugsArray, $pages)
        ));
    }
    /**
     * @param string $host
     *
     * @return string
     * @throws \Exception
     */
    protected function getHomepage($host = null)
    {
        /** @var PageRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository('OrbitaleCmsBundle:Page');

        /** @var Page|null $homepage */
        $homepage = $repo->findHomepage($host);

        if ($homepage) {
            return $homepage->getSlug();
        }
        throw $this->createNotFoundException('No homepage has been configured. Please check your existing pages or create a homepage in your backoffice.');
    }

}
