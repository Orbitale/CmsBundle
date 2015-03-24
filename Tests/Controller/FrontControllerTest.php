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

class FrontControllerTest extends AbstractTestCase
{

    public function testNoHomepage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/site/');
        $error500 = 'No homepage has been configured. Please check your existing pages or create a homepage in your backoffice. (500 Internal Server Error)';
        $this->assertEquals($error500, trim($crawler->filter('title')->html()));
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    public function testNoPageWithSlug()
    {
        $client = static::createClient();
        $client->request('GET', '/site/inexistent-slug');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testOneHomepage()
    {
        $homepage = new Page();
        $homepage
            ->setHomepage(true)
            ->setEnabled(true)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setHost('localhost')
            ->setContent('Hello world!')
        ;

        static::bootKernel();
        static::$kernel->getContainer()->get('doctrine')->getManager()->persist($homepage);
        static::$kernel->getContainer()->get('doctrine')->getManager()->flush();

        $client = static::createClient();

        $crawler = $client->request('GET', '/site/');
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('h1')->html()));
        $this->assertEquals($homepage->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/site/home');
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($homepage->getTitle(), trim($crawler->filter('h1')->html()));
        $this->assertEquals($homepage->getContent(), trim($crawler->filter('article')->html()));
    }

    public function testTree()
    {
        static::bootKernel();

        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();

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

        $client = static::createClient();

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/site/'.$childOne->getTree());
        $this->assertEquals($childOne->getTitle(), trim($crawler->filter('title')->html()));
        $this->assertEquals($childOne->getTitle(), trim($crawler->filter('h1')->html()));
        $this->assertEquals($childOne->getContent(), trim($crawler->filter('article')->html()));

        // Repeat with the homepage directly in the url
        $client->request('GET', '/site/root/second-level');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

}
