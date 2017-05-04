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
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

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
     * @param array $options An array of options to pass to the createKernel class
     *
     * @return KernelInterface
     */
    protected static function getKernel(array $options = []): KernelInterface
    {
        static::bootKernel($options);

        return static::$kernel;
    }

    /**
     * @param array $values
     *
     * @return Page
     */
    protected function createPage(array $values = []): Page
    {
        $page = new Page();

        foreach ($values as $key => $value) {
            $page->{'set'.ucfirst($key)}($value);
        }

        return $page;
    }
}
