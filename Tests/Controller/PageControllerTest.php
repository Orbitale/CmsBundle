<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;

class PageControllerTest extends AbstractTestCase
{
    public function testNoHomepage()
    {
        $error   = 'No homepage has been configured. Please check your existing pages or create a homepage in your application. (404 Not Found)';
        $client  = static::createClient();
        $crawler = $client->request('GET', '/page/');
        static::assertEquals($error, trim($crawler->filter('title')->html()));
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoPageWithSlug()
    {
        $client = static::createClient();
        $client->request('GET', '/page/inexistent-slug');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testOneHomepage()
    {
        $client = static::createClient();

        $homepage = $this->createPage([
            'homepage' => true,
            'enabled'  => true,
            'slug'     => 'home',
            'title'    => 'My homepage',
            'host'     => 'localhost',
            'content'  => 'Hello world!',
        ]);

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        $crawler = $client->request('GET', '/page/');
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url

        // First, check that any right trimming "/" will redirect
        $client->request('GET', '/page/home/');
        static::assertTrue($client->getResponse()->isRedirect('/page/home'));
        $client->followRedirect();

        // Check that the homepage with slug is redirected to the root page
        static::assertTrue($client->getResponse()->isRedirect('/page'));
        $crawler = $client->followRedirect();

        static::assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));
    }

    public function testOneHomepageWithLocale()
    {
        $client = static::createClient();

        $homepage = $this->createPage([
            'homepage' => true,
            'enabled'  => true,
            'locale'   => 'en',
            'slug'     => 'home',
            'title'    => 'My homepage',
            'host'     => 'localhost',
            'content'  => 'Hello world!',
        ]);

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        $crawler = $client->request('GET', '/page/');
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url

        // First, check that any right trimming "/" will redirect
        $client->request('GET', '/page/home/');
        static::assertTrue($client->getResponse()->isRedirect('/page/home'));
        $client->followRedirect();

        // Check that the homepage with slug is redirected to the root page
        static::assertTrue($client->getResponse()->isRedirect('/page?_locale=en'));
        $crawler = $client->followRedirect();

        static::assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));
    }

    public function testTree()
    {
        $client = static::createClient();

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        // Prepare 3 pages : the root, the first level, and the third one that's disabled
        $root = $this->createPage([
            'homepage' => true,
            'enabled'  => true,
            'slug'     => 'root',
            'title'    => 'Root',
            'content'  => 'The root page',
        ]);
        $em->persist($root);
        $em->flush();

        $childOne = $this->createPage([
            'enabled' => true,
            'slug'    => 'first-level',
            'title'   => 'First level',
            'content' => 'This page is only available in the first level',
            'parent'  => $root,
        ]);
        $em->persist($childOne);
        $em->flush();

        $childTwoDisabled = $this->createPage([
            'enabled' => false,
            'slug'    => 'second-level',
            'title'   => 'Disabled Page',
            'content' => 'This page should render a 404 error',
            'parent'  => $root,
        ]);
        $em->persist($childTwoDisabled);
        $em->flush();

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/page/root/first-level');
        static::assertEquals($childOne->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($childOne->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($childOne->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url
        $client->request('GET', '/page/root/second-level');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testMetas()
    {
        $client = static::createClient();

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        $page = $this->createPage([
            'homepage'        => true,
            'enabled'         => true,
            'title'           => 'Root',
            'content'         => 'The root page',
            'css'             => '#home{color:red;}',
            'js'              => 'alert("ok");',
            'metaDescription' => 'meta description',
            'metaKeywords'    => 'this is a meta keyword list',
            'metaTitle'       => 'this title is only in the metas',
        ]);
        $em->persist($page);
        $em->flush();

        $crawler = $client->request('GET', '/page');
        static::assertEquals($page->getTitle(), trim($crawler->filter('title')->html()));
        static::assertEquals($page->getTitle(), trim($crawler->filter('article > h1')->html()));
        static::assertContains($page->getContent(), trim($crawler->filter('article')->html()));

        static::assertEquals($page->getCss(), trim($crawler->filter('#orbitale_cms_css')->html()));
        static::assertEquals($page->getJs(), trim($crawler->filter('#orbitale_cms_js')->html()));
        static::assertEquals($page->getMetaDescription(), trim($crawler->filter('meta[name="description"]')
            ->attr('content')));
        static::assertEquals($page->getMetaKeywords(), trim($crawler->filter('meta[name="keywords"]')
            ->attr('content')));
        static::assertEquals($page->getMetaTitle(), trim($crawler->filter('meta[name="title"]')->attr('content')));
    }

    public function testParentAndChildrenDontReverse()
    {
        $client = static::createClient();
        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        $parent = $this->createPage([
            'enabled'  => true,
            'homepage' => true,
            'title'    => 'Locale+host',
            'host'     => 'localhost',
            'locale'   => 'en',
        ]);
        $em->persist($parent);
        $em->flush();

        $child = $this->createPage([
            'enabled'  => true,
            'homepage' => true,
            'title'    => 'Host only',
            'host'     => 'localhost',
            'parent'   => $parent,
        ]);
        $em->persist($child);
        $em->flush();

        $client->request('GET', '/page/'.$child->getSlug().'/'.$parent->getSlug());
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * With the locale & host matching system, the precedence of the homepage should have this order (the first being the most important):
     * - Locale & Host
     * - Host only
     * - Locale only
     * - No matching criteria
     * If there are multiple pages that match any "matching criteria", the behavior is unexpected, so we should not handle this naturally.
     */
    public function testAllTypesOfPagesForHomepage()
    {
        $client = static::createClient();

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        // First, create the pages
        /** @var Page[] $pages */
        $pages = [
            'both'   => $this->createPage([
                'enabled'  => true,
                'homepage' => true,
                'title'    => 'Locale+host',
                'host'     => 'localhost',
                'locale'   => 'en',
            ]),
            'host'   => $this->createPage([
                'enabled'  => true,
                'homepage' => true,
                'title'    => 'Host only',
                'host'     => 'localhost',
            ]),
            'locale' => $this->createPage([
                'enabled'  => true,
                'homepage' => true,
                'title'    => 'Locale only',
                'locale'   => 'en',
            ]),
            'none'   => $this->createPage([
                'enabled'  => true,
                'homepage' => true,
                'title'    => 'No match',
            ]),
        ];
        foreach ($pages as $page) {
            $em->persist($page);
        }
        $em->flush();

        // First page considered as homepage is the last one inserted.
        $pages = array_reverse($pages);

        // Loop the pages because the "$pages" array respects precedence,
        // So disabling the pages on each loop should make all assertions work.
        foreach ($pages as $key => $page) {
            $crawler = $client->request('GET', '/page/');
            static::assertEquals($page->getTitle(), trim($crawler->filter('title')->html()));
            $page->setEnabled(false);
            $em->merge($page);
            $em->flush();
        }
    }

    public function testBreadcrumbsDesign()
    {
        $client = static::createClient(['environment' => 'design_breadcrumbs']);

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        $page = $this->createPage(['enabled' => true, 'slug' => 'parent', 'title' => 'Parent page']);
        $em->persist($page);
        $em->flush();
        $pageChild = $this->createPage([
            'enabled' => true,
            'slug'    => 'child',
            'title'   => 'Child page',
            'parent'  => $page,
        ]);
        $em->persist($pageChild);
        $em->flush();

        static::assertEquals('parent/child', $pageChild->getTree());
        $crawler = $client->request('GET', '/page/'.$pageChild->getTree());

        static::assertEquals('breadcrumb-test-class', $crawler->filter('#breadcrumbs')->first()->attr('class'));

        $nodes = $crawler->filter('#breadcrumbs *');

        /** @var \DOMElement[] $nodesArray */
        $nodesArray = [];
        foreach ($nodes as $k => $node) {
            // This is a trick for SF2.3 and the lack of "getNode" method.
            $nodesArray[$k] = $node;
        }

        // First element = homepage
        $homeNode = $nodesArray[0];
        static::assertEquals('a', $homeNode->tagName);
        static::assertEquals('breadcrumb-link', $homeNode->getAttribute('class'));

        // Second element = separator
        $separator = $nodesArray[1];
        static::assertEquals('span', $separator->tagName);
        static::assertEquals('breadcrumb-overriden-separator-class', $separator->getAttribute('class'));
        static::assertEquals('|', $separator->textContent);

        // Third element = link to the parent page
        $firstLinkNode = $nodesArray[2];
        static::assertEquals('a', $firstLinkNode->tagName);
        static::assertEquals('breadcrumb-link', $firstLinkNode->getAttribute('class'));
        static::assertEquals($page->getTitle(), trim($firstLinkNode->textContent));

        // We sort of skip node 3 because it should be a separator
        static::assertEquals('breadcrumb-overriden-separator-class', $nodesArray[3]->getAttribute('class'));

        $currentLinkNode = $nodesArray[4];
        static::assertEquals('span', $currentLinkNode->tagName);
        static::assertEquals('breadcrumb-current', $currentLinkNode->getAttribute('class'));
        static::assertEquals($pageChild->getTitle(), trim($currentLinkNode->textContent));

        $crawler->clear();
    }
}
