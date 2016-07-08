<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;

/**
 * Class AbstractTestCase.
 */
class AbstractTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

    public function setUp()
    {
        /** @var Connection $c */
        $c = static::getKernel()->getContainer()->get('doctrine')->getConnection();
        $c->query('delete from orbitale_cms_pages where 1');
        $c->query('delete from orbitale_cms_categories where 1');
    }

    /**
     * @param array $options
     */
    protected static function bootKernel(array $options = array())
    {
        if (method_exists('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase', 'bootKernel')) {
            parent::bootKernel($options);
        } else {
            if (null !== static::$kernel) {
                static::$kernel->shutdown();
            }
            static::$kernel = static::createKernel($options);
            static::$kernel->boot();
            static::$kernel;
        }
    }

    /**
     * @param array $options An array of options to pass to the createKernel class
     *
     * @return KernelInterface
     */
    protected static function getKernel(array $options = array())
    {
        static::bootKernel($options);

        return static::$kernel;
    }

    /**
     * @param array $values
     *
     * @return Page
     */
    protected function createPage(array $values = array())
    {
        $page = new Page();
        foreach ($values as $key => $value) {
            $page->{'set'.ucfirst($key)}($value);
        }

        return $page;
    }
}
