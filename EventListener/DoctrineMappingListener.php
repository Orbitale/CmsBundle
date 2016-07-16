<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations in Page and Category entities,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    /**
     * @var string
     */
    private $pageClass;

    /**
     * @var string
     */
    private $categoryClass;

    public function __construct($pageClass, $categoryClass)
    {
        $this->pageClass     = $pageClass;
        $this->categoryClass = $categoryClass;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        $isPage     = is_a($classMetadata->getName(), $this->pageClass, true);
        $isCategory = is_a($classMetadata->getName(), $this->categoryClass, true);

        if ($isPage) {
            $this->processPageMetadata($classMetadata);
            $this->processParent($classMetadata, $this->pageClass);
            $this->processChildren($classMetadata, $this->pageClass);
        }

        if ($isCategory) {
            $this->processCategoryMetadata($classMetadata);
            $this->processParent($classMetadata, $this->categoryClass);
            $this->processChildren($classMetadata, $this->categoryClass);
        }
    }

    /**
     * Declare mapping for Page entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processPageMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('category')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'category',
                'targetEntity' => $this->categoryClass,
                'inversedBy'   => 'pages',
            ]);
        }
    }

    /**
     * Declare mapping for Category entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processCategoryMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('pages')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'pages',
                'targetEntity' => $this->pageClass,
                'mappedBy'     => 'category',
            ]);
        }

    }

    /**
     * Declare self-bidirectionnal mapping for parent.
     *
     * @param ClassMetadata $classMetadata
     * @param string        $class
     */
    private function processParent(ClassMetadata $classMetadata, $class)
    {
        if (!$classMetadata->hasAssociation('parent')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'parent',
                'targetEntity' => $class,
                'inversedBy'   => 'children',
            ]);
        }
    }

    /**
     * Declare self-bidirectionnal mapping for children
     *
     * @param ClassMetadata $classMetadata
     * @param string        $class
     */
    private function processChildren(ClassMetadata $classMetadata, $class)
    {
        if (!$classMetadata->hasAssociation('children')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'children',
                'targetEntity' => $class,
                'mappedBy'     => 'parent',
            ]);
        }
    }
}
