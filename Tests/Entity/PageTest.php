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
        $homepage = new Page();
        return $homepage
            ->setHomepage(true)
            ->setEnabled(false)
            ->setSlug('home')
            ->setTitle('My homepage')
            ->setHost('localhost')
            ->setContent('Hello world!')
        ;
    }

    public function testOneHomepage()
    {
        $homepage = $this->getDummyPage();

        static::bootKernel();
        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        $this->assertEquals($homepage->getTitle(), (string) $homepage);

        $this->assertFalse($homepage->isEnabled());
        $this->assertFalse($homepage->isDeleted());
        $this->assertTrue($homepage->isHomepage());
        $this->assertNull($homepage->getCategory());

        $homepage->setParent($homepage);
        $this->assertNull($homepage->getParent());
    }

    public function testLifecycleCallbacks()
    {
        $homepage = $this->getDummyPage();

        $child = $this->getDummyPage()->setSlug('child');

        $homepage->addChild($child);
        $child->setParent($homepage);

        static::bootKernel();
        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($child);
        $em->flush();

        $this->assertEquals(array($child), $homepage->getChildren()->toArray());

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->findOneBy(array('id' => $homepage->getId()));

        $this->assertNotNull($homepage);

        $em->remove($homepage);
        $em->flush();

        $homepage = $em->getRepository(get_class($homepage))->findOneBy(array('id' => $homepage->getId()));

        $this->assertNull($homepage);
        $this->assertNull($child->getParent());

    }

}
