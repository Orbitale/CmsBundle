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
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Category;
use Orbitale\Bundle\CmsBundle\Tests\Fixtures\TestBundle\Entity\Page;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryTest extends AbstractTestCase
{
    public function testCategory()
    {
        $homepage = new Page();
        $homepage->setHomepage(true);
        $homepage->setEnabled(false);
        $homepage->setSlug('home');
        $homepage->setTitle('My homepage');
        $homepage->setHost('localhost');
        $homepage->setContent('Hello world!');

        $category = new Category();
        $category->setName('Default category');
        $category->setSlug('default');

        $homepage->setCategory($category);

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($homepage);
        $em->persist($category);
        $em->flush();

        /** @var Page $homepage */
        $homepage = $em->getRepository(get_class($homepage))->find($homepage->getId());

        static::assertEquals($homepage->getCategory(), $category);
        static::assertEquals($category->getName(), (string) $category);
        static::assertFalse($category->isEnabled()); // Base value
    }

    public function testIdenticalParent()
    {
        $category = new Category();
        $category->setName('Default category');
        $category->setSlug('default');
        $category->setEnabled(true);
        $category->setParent($category);
        static::assertNull($category->getParent());
    }

    public function testLifecycleCallbacks()
    {
        $category = new Category();
        $category->setName('Default category');
        $category->setSlug('default');
        $category->setEnabled(true);

        $child = clone $category;
        $child->setSlug('child');

        $category->addChild($child);
        $child->setParent($category);

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($category);
        $em->persist($child);
        $em->flush();

        static::assertEquals([$child], $category->getChildren()->toArray());

        /** @var Category $category */
        $category = $em->getRepository(get_class($category))->findOneBy(['id' => $category->getId()]);

        static::assertNotNull($category);

        if (null !== $category) {
            $em->remove($category);
            $em->flush();
        }

        $category = $em->getRepository(get_class($category))->findOneBy(['id' => $category->getId()]);

        static::assertNull($category);
        static::assertNull($child->getParent());
    }

    public function testRemoval()
    {
        $category = new Category();

        $category->setName('Default category');
        $category->setSlug('default');
        $category->setEnabled(true);

        $child = new Category();

        $child->setName('Child category');
        $child->setSlug('child');
        $child->setEnabled(true);
        $child->setParent($category);

        $category->addChild($child);

        $kernel = static::bootKernel();

        /** @var EntityManager $em */
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->persist($category);
        $em->persist($child);
        $em->flush();

        /** @var Category $category */
        $category = $em->getRepository(get_class($category))->find($category->getId());

        $children = $category->getChildren();
        $first    = $children[0];
        static::assertEquals($child->getId(), $first->getId());

        $category->removeChild($child);
        $child->setParent(null);

        $em->remove($category);
        $em->flush();

        $child = $em->getRepository(get_class($child))->find($child->getId());

        static::assertNull($child->getParent());
    }

    public function testCategorySlugIsTransliterated()
    {
        $category = new Category();
        $category->setName('Default category');

        $category->updateSlug();

        static::assertEquals('default-category', $category->getSlug());
    }

    public function testCategorySlugIsNotTransliteratedIfEmpty()
    {
        $category = new Category();
        $category->setName('');

        $category->updateSlug();

        static::assertEquals(null, $category->getSlug());
    }

    public function testSuccessfulValidation()
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $category = new Category();
        $category->setName('Name');
        $category->setSlug('name');

        $errors = $validator->validate($category);

        self::assertCount(0, $errors);

        static::assertSame('Name', $category->getName());
        static::assertSame('name', $category->getSlug());
        static::assertNull($category->getDescription());
        static::assertNull($category->getParent());
        static::assertFalse($category->isEnabled());
    }

    public function testFailingValidationWithEmptyData()
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $category = new Category();

        $errors = $validator->validate($category);

        self::assertCount(2, $errors);

        self::assertSame('name', $errors[0]->getPropertyPath());
        self::assertSame('slug', $errors[1]->getPropertyPath());


        static::assertSame('', $category->getName());
        static::assertSame('', $category->getSlug());
        static::assertNull($category->getDescription());
        static::assertNull($category->getParent());
        static::assertFalse($category->isEnabled());
    }
}
