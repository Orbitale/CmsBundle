<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Entity;

use Behat\Transliterator\Transliterator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity("slug")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\MappedSuperclass(repositoryClass="Orbitale\Bundle\CmsBundle\Repository\CategoryRepository")
 */
abstract class Category
{
    /**
     * @return int|string
     */
    abstract public function getId();

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var Category
     */
    protected $parent;

    /**
     * @var Category[]|ArrayCollection
     */
    protected $children;

    /**
     * @var Page[]|ArrayCollection
     */
    protected $pages;

    public function __toString()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->children  = new ArrayCollection();
        $this->pages     = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Category
     */
    public function setDescription(string $description = null): Category
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug(string $slug = null): Category
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return Category
     */
    public function setEnabled(bool $enabled = null): Category
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Category|null $parent
     *
     * @return Category
     */
    public function setParent(Category $parent = null): Category
    {
        if ($parent === $this) {
            // Refuse the category to have itself as parent.
            $this->parent = null;

            return $this;
        }

        $this->parent = $parent;

        // Ensure bidirectional relation is respected.
        if ($parent && false === $parent->getChildren()->indexOf($this)) {
            $parent->addChild($this);
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $date
     *
     * @return Category
     */
    public function setCreatedAt(\DateTime $date): Category
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * @return Category[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Category $category
     *
     * @return Category
     */
    public function addChild(Category $category): Category
    {
        $this->children->add($category);

        if ($category->getParent() !== $this) {
            $category->setParent($this);
        }

        return $this;
    }

    /**
     * @param Category $child
     *
     * @return Category
     */
    public function removeChild(Category $child): Category
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return Category[]|ArrayCollection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param Page $page
     *
     * @return Category
     */
    public function addPage(Page $page): Category
    {
        $this->children->add($page);

        if ($page->getCategory() !== $this) {
            $page->setCategory($this);
        }

        return $this;
    }

    /**
     * @param Page $page
     *
     * @return Category
     */
    public function removePage(Page $page): Category
    {
        $this->children->removeElement($page);

        $page->setCategory(null);

        return $this;
    }

    /**
     * @param string $separator
     *
     * @return string
     */
    public function getTree(string $separator = '/'): string
    {
        $tree = '';

        $current = $this;
        do {
            $tree    = $current->getSlug().$separator.$tree;
            $current = $current->getParent();
        } while ($current);

        return trim($tree, $separator);
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateSlug()
    {
        if (!$this->slug) {
            $this->slug = Transliterator::transliterate($this->name);
        }
    }

    /**
     * @ORM\PreRemove()
     *
     * @param LifecycleEventArgs $event
     */
    public function onRemove(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        if (count($this->children)) {
            foreach ($this->children as $child) {
                $child->setParent(null);
                $em->persist($child);
            }
        }
        $this->enabled = false;
        $this->parent  = null;
        $this->name .= '-'.$this->getId().'-deleted';
        $this->slug .= '-'.$this->getId().'-deleted';
    }
}
