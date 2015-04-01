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
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;

class PageControllerTest extends AbstractTestCase
{

    public function testNoHomepage()
    {
        $error = 'No homepage has been configured. Please check your existing pages or create a homepage in your backoffice. (404 Not Found)';
        $client = static::createClient();
        $crawler = $client->request('GET', '/page/');
        $this->assertEquals($error, trim($crawler->filter('title')->html()));
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoPageWithSlug()
    {
        $client = static::createClient();
        $client->request('GET', '/page/inexistent-slug');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testOneHomepage()
    {
        $client = static::createClient();

        $homepage = new Page();
        $homepage
            ->setHomepage(true)
            ->setEnabled(true)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setHost('localhost')
            ->setContent('Hello world!')
        ;

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        $crawler = $client->request('GET', '/page/');
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        $this->assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/page/home');
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('article > h1')->html()));
        $this->assertContains($homepage->getContent(), trim($crawler->filter('article')->html()));
    }

    public function testTree()
    {
        $client = static::createClient();

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        // Prepare 3 pages : the root, the first level, and the third one that's disabled
        $parent = new Page();
        $parent
            ->setEnabled(true)
            ->setSlug('root')
            ->setTitle('Root')
            ->setContent('The root page')
            ->setDeletedAt(null)
        ;
        $em->persist($parent);
        $em->flush();

        $childOne = new Page();
        $childOne
            ->setEnabled(true)
            ->setSlug('first-level')
            ->setTitle('First level')
            ->setContent('This page is only available in the first level')
            ->setParent($parent)
            ->setDeletedAt(null)
        ;
        $em->persist($childOne);
        $em->flush();

        $childTwoDisabled = new Page();
        $childTwoDisabled
            ->setEnabled(false)
            ->setSlug('second-level')
            ->setTitle('Disabled Page')
            ->setContent('This page should render a 404 error')
            ->setParent($parent)
            ->setDeletedAt(null)
        ;
        $em->persist($childTwoDisabled);
        $em->flush();

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/page/'.$childOne->getTree());
        $this->assertEquals($childOne->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($childOne->getTitle(), trim($crawler->filter('article > h1')->html()));
        $this->assertContains($childOne->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url
        $client->request('GET', '/page/root/second-level');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testMetas()
    {
        $client = static::createClient();

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();

        $page = new Page();
        $page
            ->setEnabled(true)
            ->setSlug('root')
            ->setTitle('Root')
            ->setContent('The root page')
            ->setDeletedAt(null)
            ->setCss('#home{color:red;}')
            ->setJs('alert("ok");')
            ->setMetaDescription('meta descri')
            ->setMetaKeywords('this is a meta keyword list')
            ->setMetaTitle('this title is only in the metas')
        ;
        $em->persist($page);
        $em->flush();

        $crawler = $client->request('GET', '/page/'.$page->getTree());
        $this->assertEquals($page->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($page->getTitle(), trim($crawler->filter('article > h1')->html()));
        $this->assertContains($page->getContent(), trim($crawler->filter('article')->html()));

        $this->assertEquals($page->getCss(), trim($crawler->filter('#orbitale_cms_css')->html()));
        $this->assertEquals($page->getJs(), trim($crawler->filter('#orbitale_cms_js')->html()));
        $this->assertEquals($page->getMetaDescription(), trim($crawler->filter('meta[name="description"]')->attr('content')));
        $this->assertEquals($page->getMetaKeywords(), trim($crawler->filter('meta[name="keywords"]')->attr('content')));
        $this->assertEquals($page->getMetaTitle(), trim($crawler->filter('meta[name="title"]')->attr('content')));

    }

}
