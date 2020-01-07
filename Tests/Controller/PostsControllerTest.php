<?php

namespace Orbitale\Bundle\CmsBundle\Tests\Controller;

use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;

class PostsControllerTest extends AbstractTestCase
{

    public function testNoDatetimeInURL(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public static function testIncompleteDateTimeInURL(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/2020/01/');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testNoPostWithSlug(): void
    {
        $client = static::createClient();
        $client->request('GET', '/posts/2019/12/19/notexists');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
