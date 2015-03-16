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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class FrontController extends Controller
{

    /**
     * @Route("/{slugs}", name="cms_home", requirements={"slugs": "([a-zA-Z0-9_-]+\/?)*"}, defaults={"slugs": ""})
     */
    public function indexAction($slugs = '', Request $request)
    {
        if (!$slugs) {
            $slugs = $this->getHomepage($request->getHost());
        }

        $slugsArray = explode('/', $slugs);

        /** @var PageRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository('OrbitaleCmsBundle:Page');

        /** @var Page[] $pages */
        $pages = $repo->findBy(array('slug' => $slugsArray));

        return $this->render('OrbitaleCmsBundle:Front:index.html.twig', array(
            'pages' => $pages,
            'page'  => $this->getFinalPage($slugsArray, $pages)
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
        throw new \Exception('No homepage has been configured. Please check your existing pages or create a homepage in your backoffice.');
    }

    /**
     * @param array  $slugs
     * @param Page[] $pages
     *
     * @return Page
     */
    protected function getFinalPage(array $slugs, array $pages)
    {
        if (count($slugs) !== count($pages)) {
            throw $this->createNotFoundException();
        }
        foreach ($slugs as $k => $slug) {
            if (
                isset($pages[$k])
                && $pages[$k]->getSlug() === $slug
                && $pages[$k]->isEnabled()
                && (
                    (isset($slugs[$k - 1]) && $pages[$k]->getParent()->getId() === $pages[$k - 1]->getId())
                    || (!isset($slugs[$k - 1]) && !$pages[$k]->getParent())
                )
            ) {
                continue;
            } else {
                throw $this->createNotFoundException();
            }
        }
        return array_pop($pages);
    }

}
