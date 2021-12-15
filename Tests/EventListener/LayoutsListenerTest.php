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

use Doctrine\ORM\EntityManagerInterface;
use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Twig\Environment;
use Twig\Error\LoaderError;

class LayoutsListenerTest extends AbstractTestCase
{
    public function testDifferentLayout(): void
    {
        $client = self::createClient(['environment' => 'layout']);

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);
        $twig->resolveTemplate('test_layout.html.twig');

        $crawler = $client->request('GET', '/page/');

        static::assertEquals(1, $crawler->filter('#test_layout_wrapper')->count());
        static::assertMatchesRegularExpression('~^One change of the layout is this special hardcoded title\. ~', $crawler->filter('title')->html());
    }

    public function testHostLayout(): void
    {
        $client = self::createClient(['environment' => 'layout'], ['HTTP_HOST' => 'local.host']);

        $crawler = $client->request('GET', '/page/');

        static::assertMatchesRegularExpression('~^This layout is only for local\.host\. ~', $crawler->filter('title')->html());
    }

    public function testLayoutWrong(): void
    {
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Unable to find template this_layout_does_not_exist.html.twig for layout front. The "layout" parameter must be a valid twig view to be used as a layout in "this_layout_does_not_exist.html.twig".');
        self::createClient(['environment' => 'layout_wrong'])->request('GET', '/page/');
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $client = parent::createClient($options, $server);

        $homepage = new Page();

        $homepage->setHomepage(true);
        $homepage->setEnabled(true);
        $homepage->setSlug('home');
        $homepage->setTitle('My homepage');
        $homepage->setContent('Hello world!');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($homepage);
        $em->flush();

        return $client;
    }
}
