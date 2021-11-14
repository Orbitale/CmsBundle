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
use Orbitale\Bundle\CmsBundle\Tests\AbstractTestCase;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PageTest extends AbstractTestCase
{
    public function getDummyPage(): Page
    {
        $page = new Page();

        $page->setHomepage(true);
        $page->setSlug('home');
        $page->setTitle('My homepage');
        $page->setHost('localhost');
        $page->setContent('Hello world!');

        return $page;
    }

    public function testOneHomepage()
    {
        $homepage = $this->getDummyPage();

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        static::assertEquals($homepage->getTitle(), (string) $homepage);

        static::assertFalse($homepage->isEnabled()); // Base value in entity
        static::assertTrue($homepage->isHomepage());
        static::assertEquals('localhost', $homepage->getHost());
        static::assertInstanceOf(\DateTimeImmutable::class, $homepage->getCreatedAt());

        $homepage->setParent($homepage);
        static::assertNull($homepage->getParent());
    }

    public function testLifecycleCallbacks()
    {
        $homepage = $this->getDummyPage();

        $child = $this->getDummyPage();
        $child->setSlug('child');

        $homepage->addChild($child);
        $child->setParent($homepage);

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($child);
        $em->flush();

        static::assertEquals([$child], $homepage->getChildren()->toArray());

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->findOneBy(['id' => $homepage->getId()]);

        static::assertNotNull($homepage);

        if (null !== $homepage) {
            $em->remove($homepage);
            $em->flush();
        }

        $homepage = $em->getRepository(get_class($homepage))->findOneBy(['id' => $homepage->getId()]);

        static::assertNull($homepage);
        static::assertNull($child->getParent());
    }

    public function testRemoval()
    {
        $page = new Page();
        $page->setTitle('Default page');
        $page->setSlug('default');
        $page->setEnabled(true);

        $child = new Page();
        $child->setTitle('Child page');
        $child->setSlug('child');
        $child->setEnabled(true);
        $child->setParent($page);

        $page->addChild($child);

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($page);
        $em->persist($child);
        $em->flush();

        $page = $em->getRepository(get_class($page))->find($page->getId());

        $children = $page->getChildren();
        /** @var Page $first */
        $first = $children[0];
        static::assertEquals($child->getId(), $first->getId());

        $page->removeChild($child);
        $child->setParent(null);

        $em->remove($page);
        $em->flush();

        $child = $em->getRepository(get_class($child))->find($child->getId());

        static::assertNull($child->getParent());
    }

    public function testPageSlugIsTransliterated()
    {
        $page = new Page();
        $page->setTitle('Default page');

        $page->updateSlug();

        static::assertEquals('default-page', $page->getSlug());
    }

    public function testSuccessfulValidation()
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $page = new Page();
        $page->setTitle('Title');
        $page->setSlug('title');

        $errors = $validator->validate($page);

        self::assertCount(0, $errors);

        static::assertSame('Title', $page->getTitle());
        static::assertSame('title', $page->getSlug());
        static::assertNull($page->getMetaDescription());
        static::assertNull($page->getMetaTitle());
        static::assertNull($page->getMetaKeywords());
        static::assertNull($page->getHost());
        static::assertNull($page->getContent());
        static::assertNull($page->getCss());
        static::assertNull($page->getJs());
        static::assertNull($page->getCategory());
        static::assertNull($page->getParent());
        static::assertFalse($page->isHomepage());
        static::assertFalse($page->isEnabled());
    }

    public function testFailingfulValidationWithEmptyData()
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $page = new Page();

        /** @var ConstraintViolationInterface[]&ConstraintViolationListInterface $errors */
        $errors = $validator->validate($page);

        self::assertCount(2, $errors);

        self::assertSame('title', $errors[0]->getPropertyPath());
        self::assertSame('slug', $errors[1]->getPropertyPath());

        static::assertSame('', $page->getTitle());
        static::assertSame('', $page->getSlug());
        static::assertNull($page->getMetaDescription());
        static::assertNull($page->getMetaTitle());
        static::assertNull($page->getMetaKeywords());
        static::assertNull($page->getHost());
        static::assertNull($page->getContent());
        static::assertNull($page->getCss());
        static::assertNull($page->getJs());
        static::assertNull($page->getCategory());
        static::assertNull($page->getParent());
        static::assertFalse($page->isHomepage());
        static::assertFalse($page->isEnabled());
    }
}
