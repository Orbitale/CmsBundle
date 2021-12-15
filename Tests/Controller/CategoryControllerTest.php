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

use Doctrine\ORM\EntityManagerInterface;
use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;

class CategoryControllerTest extends AbstractTestCase
{
    public function testNoCategoryWithSlug()
    {
        $client = self::createClient();
        $client->request('GET', '/category/inexistent-slug');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testSingleCategory()
    {
        $client = self::createClient();

        $category = new Category();
        $category->setSlug('default');
        $category->setName('Default Category');
        $category->setDescription('Hello world!');
        $category->setEnabled(true);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($category);
        $em->flush();

        // Repeat with the homepage directly in the url
        // First, check that any right trimming "/" will redirect
        $client->request('GET', '/category/default/');
        static::assertTrue($client->getResponse()->isRedirect('/category/default'));

        $crawler = $client->followRedirect();
        static::assertEquals($category->getName(), trim($crawler->filter('title')->html()));
        static::assertEquals($category->getName(), trim($crawler->filter('article > h1')->html()));
        static::assertStringContainsString($category->getDescription(), trim($crawler->filter('article')->first()->html()));
    }

    public function testTree()
    {
        $client = self::createClient();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // Prepare 3 pages : the root, the first level, and the third one that's disabled
        $parent = new Category();
        $parent->setSlug('default');
        $parent->setName('Default Category');
        $parent->setDescription('Hello world!');
        $parent->setEnabled(true);

        $em->persist($parent);
        $em->flush();

        $childOne = new Category();
        $childOne->setEnabled(true);
        $childOne->setSlug('first-level');
        $childOne->setName('First level');
        $childOne->setDescription('This level is the first one');
        $childOne->setParent($parent);

        $em->persist($childOne);
        $em->flush();

        $childTwoDisabled = new Category();
        $childTwoDisabled->setEnabled(false);
        $childTwoDisabled->setSlug('second-level');
        $childTwoDisabled->setName('Disabled category');
        $childTwoDisabled->setDescription('This category should render a 404 error');
        $childTwoDisabled->setParent($parent);

        $em->persist($childTwoDisabled);
        $em->flush();

        // Repeat with the homepage directly in the url
        $crawler = $client->request('GET', '/category/'.$childOne->getTree());
        static::assertEquals($childOne->getName(), trim($crawler->filter('title')->html()));
        static::assertEquals($childOne->getName(), trim($crawler->filter('article > h1')->html()));
        static::assertStringContainsString($childOne->getDescription(), trim($crawler->filter('article')->first()->html()));

        // Repeat with the homepage directly in the url
        $client->request('GET', '/category/root/second-level');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testWithPages()
    {
        $client = self::createClient();

        $category = new Category();
        $category->setSlug('default');
        $category->setName('Default Category');
        $category->setDescription('Hello world!');
        $category->setEnabled(true);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($category);
        $em->flush();

        $page1 = new Page();
        $page1->setEnabled(true);
        $page1->setSlug('home');
        $page1->setTitle('My homepage');
        $page1->setHost('localhost');
        $page1->setContent('Hello world!');
        $page1->setCategory($category);

        $page2 = new Page();
        $page2->setEnabled(true);
        $page2->setSlug('about');
        $page2->setTitle('About page');
        $page2->setHost('localhost');
        $page2->setContent('We.are.the.robots.');
        $page2->setCategory($category);

        $em->persist($page1);
        $em->persist($page2);
        $em->flush();

        $crawler = $client->request('GET', '/category/'.$category->getTree());

        $section1 = $crawler->filter('section')->eq(0);
        static::assertEquals($page1->getTitle(), trim($section1->filter('article > h2 > a')->html()));
        static::assertStringContainsString($page1->getContent(), trim($section1->filter('article')->html()));

        $section2 = $crawler->filter('section')->eq(1);
        static::assertEquals($page2->getTitle(), trim($section2->filter('article > h2 > a')->html()));
        static::assertStringContainsString($page2->getContent(), trim($section2->filter('article')->html()));
    }
}
