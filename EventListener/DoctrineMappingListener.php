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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

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


        if (is_a($classMetadata->getName(), $this->pageClass, true) && !$classMetadata->hasAssociation('parent')) {
            // Declare mapping for category
            $classMetadata->mapManyToOne([
                'fieldName'    => 'category',
                'targetEntity' => $this->categoryClass,
                'inversedBy'   => 'pages',
            ]);

            // Declare self-bidirectionnal mapping for parent/children
            $classMetadata->mapManyToOne([
                'fieldName'    => 'parent',
                'targetEntity' => $this->pageClass,
                'inversedBy'   => 'children',
            ]);
            $classMetadata->mapOneToMany([
                'fieldName'    => 'children',
                'targetEntity' => $this->pageClass,
                'mappedBy'     => 'parent',
            ]);
        }

        if (is_a($classMetadata->getName(), $this->categoryClass, true) && !$classMetadata->hasAssociation('parent')) {
            // Declare self-bidirectionnal mapping for parent/children
            $classMetadata->mapManyToOne([
                'fieldName'    => 'parent',
                'targetEntity' => $this->categoryClass,
                'inversedBy'   => 'children',
            ]);
            $classMetadata->mapOneToMany([
                'fieldName'    => 'children',
                'targetEntity' => $this->categoryClass,
                'mappedBy'     => 'parent',
            ]);

        }
    }
}
