<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests;

use Doctrine\DBAL\Connection;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractTestCase extends WebTestCase
{
    public function setUp(): void
    {
        static::bootKernel();

        /** @var Connection $c */
        $c = static::$container->get(Connection::class);
        $c->query('delete from orbitale_cms_pages where 1');
        $c->query('delete from orbitale_cms_categories where 1');
        static::ensureKernelShutdown();
    }

    protected function createPage(array $values = []): Page
    {
        $page = new Page();

        foreach ($values as $key => $value) {
            $page->{'set'.ucfirst($key)}($value);
        }

        return $page;
    }
}
