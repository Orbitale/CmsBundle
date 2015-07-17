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
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\AbstractTestCase;

class PageTest extends AbstractTestCase
{

    public function getDummyPage()
    {
        $page = new Page();
        return $page
            ->setHomepage(true)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setHost('localhost')
            ->setContent('Hello world!')
        ;
    }

    public function testOneHomepage()
    {
        $homepage = $this->getDummyPage();

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        $this->assertEquals($homepage->getTitle(), (string) $homepage);

        $this->assertFalse($homepage->isEnabled()); // Base value in entity
        $this->assertFalse($homepage->isDeleted());
        $this->assertTrue($homepage->isHomepage());
        $this->assertEquals('localhost', $homepage->getHost());

        $homepage->setParent($homepage);
        $this->assertNull($homepage->getParent());
    }

    public function testLifecycleCallbacks()
    {
        $homepage = $this->getDummyPage();

        $child = $this->getDummyPage()->setSlug('child');

        $homepage->addChild($child);
        $child->setParent($homepage);

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($child);
        $em->flush();

        $this->assertEquals(array($child), $homepage->getChildren()->toArray());

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->findOneBy(array('id' => $homepage->getId()));

        $this->assertNotNull($homepage);

        if (null !== $homepage) {
            $em->remove($homepage);
            $em->flush();
        }

        $homepage = $em->getRepository(get_class($homepage))->findOneBy(array('id' => $homepage->getId()));

        $this->assertNull($homepage);
        $this->assertNull($child->getParent());

    }

    public function testRemoval()
    {
        $page = new Page();
        $page
            ->setTitle('Default page')
            ->setSlug('default')
            ->setEnabled(true)
        ;

        $child = new Page();
        $child
            ->setTitle('Child page')
            ->setSlug('child')
            ->setEnabled(true)
            ->setParent($page)
        ;
        $page->addChild($child);

        $kernel = static::getKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($page);
        $em->persist($child);
        $em->flush();

        $page = $em->getRepository(get_class($page))->find($page->getId());

        $children = $page->getChildren();
        /** @var Page $first */
        $first = $children[0];
        $this->assertEquals($child->getId(), $first->getId());

        $page->removeChild($child);
        $child->setParent(null);

        $em->remove($page);
        $em->flush();

        $child = $em->getRepository(get_class($child))->find($child->getId());

        $this->assertNull($child->getParent());
    }

}
