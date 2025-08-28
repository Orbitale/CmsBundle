<?php

declare(strict_types=1);

/*
 * This file is part of the OrbitaleCmsBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("slug")
 */
#[UniqueEntity('slug')]
abstract class Category
{
    /**
     * @var string
     *
     * @Assert\Type("string")
     *
     * @Assert\NotBlank()
     */
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    protected $name;

    /**
     * @var string
     *
     * @Assert\Type("string")
     *
     * @Assert\NotBlank()
     */
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    protected $slug;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type('string')]
    protected $description;

    /**
     * @var bool
     *
     * @Assert\Type("bool")
     */
    #[Assert\Type('bool')]
    protected $enabled = false;

    /**
     * @var Category
     *
     * @Assert\Type(Category::class)
     */
    #[Assert\Type(self::class)]
    protected $parent;

    /**
     * @var ArrayCollection|Category[]
     */
    protected $children;

    /**
     * @var ArrayCollection|Page[]
     */
    protected $pages;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return int|string
     */
    abstract public function getId();

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): string
    {
        return (string) $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = (string) $slug;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled = false): void
    {
        $this->enabled = (bool) $enabled;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        if ($parent === $this) {
            // Refuse the category to have itself as parent.
            $this->parent = null;

            return;
        }

        $this->parent = $parent;

        // Ensure bidirectional relation is respected.
        if ($parent && false === $parent->getChildren()->indexOf($this)) {
            $parent->addChild($this);
        }
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(self $category): void
    {
        $this->children->add($category);

        if ($category->getParent() !== $this) {
            $category->setParent($this);
        }
    }

    public function removeChild(self $child): void
    {
        $this->children->removeElement($child);
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    public function addPage(Page $page): void
    {
        $this->children->add($page);

        if ($page->getCategory() !== $this) {
            $page->setCategory($this);
        }
    }

    public function removePage(Page $page): void
    {
        $this->children->removeElement($page);

        $page->setCategory(null);
    }

    public function getTree(string $separator = '/'): string
    {
        $tree = '';

        $current = $this;
        do {
            $tree = $current->getSlug().$separator.$tree;
            $current = $current->getParent();
        } while ($current);

        return \trim($tree, $separator);
    }

    public function updateSlug(): void
    {
        if (!$this->slug) {
            $this->slug = \mb_strtolower((new AsciiSlugger())->slug($this->name)->toString());
        }
    }

    public function onRemove(PreRemoveEventArgs $event): void
    {
        $em = $event->getObjectManager();
        if (\count($this->children)) {
            foreach ($this->children as $child) {
                $child->setParent(null);
                $em->persist($child);
            }
        }
        $this->enabled = false;
        $this->parent = null;
        $this->name .= '-'.$this->getId().'-deleted';
        $this->slug .= '-'.$this->getId().'-deleted';
    }
}
