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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("slug")
 */
#[UniqueEntity("slug")]
abstract class Page
{
    /**
     * @return int|string
     */
    abstract public function getId();

    /**
     * @var string
     *
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    #[Assert\Type("string")]
    #[Assert\NotBlank]
    protected $title;

    /**
     * @var string
     *
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    #[Assert\Type("string")]
    #[Assert\NotBlank]
    protected $slug;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $content;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $metaDescription;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $metaTitle;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $metaKeywords;

    /**
     * @var null|Category
     *
     * @Assert\Type(Category::class)
     */
    #[Assert\Type(Category::class)]
    protected $category;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $css;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $js;

    /**
     * @var \DateTimeImmutable
     *
     * @Assert\Type(\DateTimeImmutable::class)
     */
    #[Assert\Type(\DateTimeImmutable::class)]
    protected $createdAt;

    /**
     * @var bool
     *
     * @Assert\Type("bool")
     */
    #[Assert\Type("bool")]
    protected $enabled = false;

    /**
     * @var bool
     *
     * @Assert\Type("bool")
     */
    #[Assert\Type("bool")]
    protected $homepage = false;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $host;

    /**
     * @var string
     *
     * @Assert\Type("string")
     */
    #[Assert\Type("string")]
    protected $locale;

    /**
     * @var null|Page
     *
     * @Assert\Type(Page::class)
     */
    #[Assert\Type(Page::class)]
    protected $parent;

    /**
     * @var Page[]|ArrayCollection
     */
    protected $children;

    public function __toString()
    {
        return $this->title;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->children  = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return (string) $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = (string) $title;
    }

    public function getSlug(): string
    {
        return (string) $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = (string) $slug;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(?string $css): void
    {
        $this->css = $css;
    }

    public function getJs(): ?string
    {
        return $this->js;
    }

    public function setJs(?string $js): void
    {
        $this->js = $js;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $date): void
    {
        if ($date instanceof \DateTime) {
            $date = \DateTimeImmutable::createFromMutable($date);
        }

        $this->createdAt = $date;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled = false): void
    {
        $this->enabled = (bool) $enabled;
    }

    public function getParent(): ?Page
    {
        return $this->parent;
    }

    public function setParent(?Page $parent): void
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
     * @return Page[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(Page $page): void
    {
        $this->children->add($page);

        if ($page->getParent() !== $this) {
            $page->setParent($this);
        }
    }

    public function removeChild(Page $page): void
    {
        $this->children->removeElement($page);
    }

    public function isHomepage(): bool
    {
        return $this->homepage;
    }

    public function setHomepage(bool $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

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

    public function updateSlug(): void
    {
        if (!$this->slug) {
            $this->slug = mb_strtolower((new AsciiSlugger())->slug($this->title)->toString());
        }
    }

    public function onRemove(PreRemoveEventArgs $event): void
    {
        $em = $event->getObjectManager();
        if (count($this->children)) {
            foreach ($this->children as $child) {
                $child->setParent(null);
                $em->persist($child);
            }
        }
        $this->enabled = false;
        $this->parent  = null;
        $this->title .= '-'.$this->getId().'-deleted';
        $this->slug .= '-'.$this->getId().'-deleted';
    }
}
