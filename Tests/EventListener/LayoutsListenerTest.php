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
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;

class LayoutsListenerTest extends AbstractTestCase
{
    public function testDifferentLayout()
    {
        $client = static::createClient(['environment' => 'layout']);

        /** @var \Twig_Environment $twig */
        $twig = $client->getContainer()->get('twig');
        $twig->resolveTemplate('test_layout.html.twig');

        $crawler = $client->request('GET', '/page/');

        static::assertEquals(1, $crawler->filter('#test_layout_wrapper')->count());
        static::assertRegExp('~^One change of the layout is this special hardcoded title\. ~', $crawler->filter('title')->html());
    }

    public function testHostLayout()
    {
        $client = static::createClient(['environment' => 'layout'], ['HTTP_HOST' => 'local.host']);

        $crawler = $client->request('GET', '/page/');

        static::assertRegExp('~^This layout is only for local\.host\. ~', $crawler->filter('title')->html());
    }

    /**
     * @expectedException \Twig_Error_Loader
     * @expectedExceptionMessage Unable to find template this_layout_does_not_exist.html.twig for layout front. The "layout" parameter must be a valid twig view to be used as a layout in "this_layout_does_not_exist.html.twig".
     */
    public function testLayoutWrong()
    {
        static::createClient(['environment' => 'layout_wrong'])->request('GET', '/page/');
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = [], array $server = [])
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
