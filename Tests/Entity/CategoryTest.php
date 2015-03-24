<?php
/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Orbitale\Bundle\CmsBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;

class CategoryTest extends AbstractTestCase
{

    public function testCategory()
    {
        $homepage = new Page();
        $homepage
            ->setHomepage(true)
            ->setEnabled(false)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setHost('localhost')
            ->setContent('Hello world!')
        ;

        $category = new Category();
        $category
            ->setName('Default category')
            ->setSlug('default')
            ->setEnabled(true)
        ;

        $homepage->setCategory($category);

        /** @var EntityManager $em */
        $em = static::getKernel()->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($category);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        $this->assertEquals($homepage->getCategory(), $category);
    }

}
