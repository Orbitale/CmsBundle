<?php

namespace Orbitale\Bundle\CmsBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;

class PostsControllerTest extends AbstractTestCase
{
    public function testNoSlug(): void
    {
        $client = self::createClient();
        $client->request('GET', '/posts/2020-01-19/');
        static::assertResponseStatusCodeSame(404);
        static::assertPageTitleContains('No page identifier provided');
    }

    public function testNoPostWithSlug(): void
    {
        $client = self::createClient();
        $client->request('GET', '/posts/2019-12-19/inexistent-slug');
        static::assertResponseStatusCodeSame(404);
        static::assertPageTitleContains('Post not found');
    }

    public function testInvalidDateFormat(): void
    {
        $client = self::createClient();
        $client->request('GET', '/posts/0-0-0/inexistent-slug');
        static::assertResponseStatusCodeSame(404);
        static::assertPageTitleContains('Invalid date format provided');
    }

    public function testDateUrlDoesNotMatchPageDate(): void
    {
        $client = self::createClient();

        $page = $this->createPage([
            'createdAt' => new \DateTimeImmutable(),
            'slug' => 'test_slug',
            'title' => 'Test title',
            'enabled' => true,
        ]);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($page);
        $em->flush();

        $client->request('GET', sprintf("/posts/%s/%s", '2020-01-01', $page->getSlug()));
        static::assertResponseStatusCodeSame(404);
        static::assertPageTitleContains('Date in URL does not match post\'s date.');
    }

    public function testSuccessfulPost(): void
    {
        $client = self::createClient();

        $page = $this->createPage([
            'createdAt' => $now = new \DateTimeImmutable(),
            'slug' => 'test_slug',
            'title' => 'Test title',
            'enabled' => true,
        ]);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($page);
        $em->flush();

        $client->request('GET', sprintf("/posts/%s/%s", $now->format('Y-m-d'), $page->getSlug()));
        static::assertResponseStatusCodeSame(200);
        static::assertPageTitleContains($page->getTitle());
    }
}
