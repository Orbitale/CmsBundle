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

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($category);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        $this->assertEquals($homepage->getCategory(), $category);
        $this->assertEquals($category->getName(), (string) $category);
    }

    public function testIdenticalParent()
    {
        $category = new Category();
        $category
            ->setName('Default category')
            ->setSlug('default')
            ->setEnabled(true)
        ;
        $category->setParent($category);
        $this->assertNull($category->getParent());
    }

    public function testLifecycleCallbacks()
    {
        $category = new Category();
        $category
            ->setName('Default category')
            ->setSlug('default')
            ->setEnabled(true)
        ;

        $child = clone $category;
        $child->setSlug('child');

        $category->addChild($child);
        $child->setParent($category);

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($category);
        $em->persist($child);
        $em->flush();

        $this->assertEquals(array($child), $category->getChildren()->toArray());

        /** @var Category $category */
        $category = $em->getRepository(get_class($category))->findOneBy(array('id' => $category->getId()));

        $this->assertNotNull($category);

        $em->remove($category);
        $em->flush();

        $category = $em->getRepository(get_class($category))->findOneBy(array('id' => $category->getId()));

        $this->assertNull($category);
        $this->assertNull($child->getParent());

    }

    public function testRemoval()
    {
        $category = new Category();
        $category
            ->setName('Default category')
            ->setSlug('default')
            ->setEnabled(true)
        ;

        $child = new Category();
        $child
            ->setName('Child category')
            ->setSlug('child')
            ->setEnabled(true)
            ->setParent($category)
        ;
        $category->addChild($child);

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($category);
        $em->persist($child);
        $em->flush();

        $category = $em->getRepository(get_class($category))->find($category->getId());

        $children = $category->getChildren();
        $first = $children[0];
        $this->assertEquals($child->getId(), $first->getId());

        $category->removeChild($child);
        $child->setParent(null);

        $em->remove($category);
        $em->flush();

        $child = $em->getRepository(get_class($child))->find($child->getId());

        $this->assertNull($child->getParent());
    }
}
