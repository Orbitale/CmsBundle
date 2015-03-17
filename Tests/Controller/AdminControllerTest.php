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

class AdminControllerTest extends AbstractTestCase
{

    public function setUp()
    {
        parent::setUp();
        // Check security context, because of deprecation
        try {
            $this->getKernel()->getContainer()->get('security.context');
        } catch (\Exception $baseException) {
            $e = $baseException;
            do {
                if ($e instanceof \PHPUnit_Framework_Error_Deprecated) {
                    $this->markTestSkipped('Skip this error while Symfony SecurityContext is used by Twig\'s AppVariable.');
                }
                $e = $e->getPrevious();
            } while ($e);
        }
    }

    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/admin/?action=list&entity=Page');

        $this->assertTrue($crawler->filter('html:contains("Easy Admin")')->count() > 0);
    }

}
