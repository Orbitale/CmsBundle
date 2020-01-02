<?php

namespace Controller;

use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;

class PostsControllerTest extends AbstractTestCase
{

    public function testNoYearInURL(): void
    {
        $client  = static::createClient();
        $client->request('GET', '/posts/');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoMonthInURL(): void
    {
        $client  = static::createClient();
        $client->request('GET', '/posts/2019/');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoDayInURL(): void
    {
        $client  = static::createClient();
        $client->request('GET', '/posts/2019/12/');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoPostWithSlug(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/2019/12/19/notexists');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
