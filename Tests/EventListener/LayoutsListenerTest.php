<?php
/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;

class LayoutsListenerTest extends AbstractTestCase
{

    public function testDifferentLayout()
    {
        $client = static::createClient(array('environment' => 'layout'));

        $crawler = $client->request('GET', '/page/');

        $this->assertEquals(1, $crawler->filter('#test_layout_wrapper')->count());
        $this->assertRegExp('~^One change of the layout is this special hardcoded title\. ~', $crawler->filter('title')->html());
    }

    public function testHostLayout()
    {
        $client = static::createClient(array('environment' => 'layout'), array('HTTP_HOST' => 'local.host'));

        $crawler = $client->request('GET', '/page/');

        $this->assertRegExp('~^This layout is only for local\.host\. ~', $crawler->filter('title')->html());
    }

    /**
     * @expectedException
     * @expectedExceptionMessage
     */
    public function testLayoutWrong()
    {
        $exception = false;
        $message = false;
        try {
            static::createClient(array('environment' => 'layout_wrong'))->request('GET', '/page/');
        } catch (\Exception $e) {
            do {
                if ($e instanceof \Twig_Error_Loader) {
                    $exception = true;
                    $message = $e->getMessage() === 'Unable to find template ::this_layout_does_not_exist.html.twig for layout front. The "layout" parameter must be a valid twig view to be used as a layout.';
                }
                $e = $e->getPrevious();
            } while ($e);
        }

        $this->assertTrue($exception);
        $this->assertTrue($message);
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        $client = parent::createClient($options, $server);

        $homepage = new Page();
        $homepage
            ->setHomepage(true)
            ->setEnabled(true)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setContent('Hello world!')
        ;

        /** @var EntityManager $em */
        $em = $client->getKernel()->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        return $client;
    }
}
