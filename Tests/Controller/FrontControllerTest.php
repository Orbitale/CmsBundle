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

use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;

class FrontControllerTest extends AbstractTestCase
{

    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/site/');

        $this->assertTrue($crawler->filter('html')->count() > 0);
    }

}
