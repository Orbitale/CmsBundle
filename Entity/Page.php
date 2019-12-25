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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("slug")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\MappedSuperclass(repositoryClass="Orbitale\Bundle\CmsBundle\Repository\PageRepository")
 */
abstract class Page
{
    /**
     * @return int|string
     */
    abstract public function getId();

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     *
     * @Assert\Type("string")
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="page_content", type="text", nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", length=255, nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $metaKeywords;

    /**
     * @var null|Category
     *
     * @Assert\Type(Category::class)
     */
    protected $category;

    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $css;

    /**
     * @var string
     *
     * @ORM\Column(name="js", type="text", nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $js;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Assert\Type(\DateTime::class)
     */
    protected $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     *
     * @Assert\Type("bool")
     */
    protected $enabled = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="homepage", type="boolean")
     *
     * @Assert\Type("bool")
     */
    protected $homepage = false;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $host;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=6, nullable=true)
     *
     * @Assert\Type("string")
     */
    protected $locale;

    /**
     * @var null|Page
     *
     * @Assert\Type(Page::class)
     */
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
        $this->createdAt = new \DateTime();
        $this->children  = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Page
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): Page
    {
        $this->slug = (string) $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content = null): Page
    {
        $this->content = $content;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription = null): Page
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle = null): Page
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(string $metaKeywords = null): Page
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category = null): Page
    {
        $this->category = $category;

        return $this;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(string $css = null): Page
    {
        $this->css = $css;

        return $this;
    }

    public function getJs(): ?string
    {
        return $this->js;
    }

    public function setJs(string $js = null): Page
    {
        $this->js = $js;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $date): Page
    {
        $this->createdAt = $date;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled = false): Page
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getParent(): ?Page
    {
        return $this->parent;
    }

    public function setParent(Page $parent = null): Page
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
     * @return Page[]|ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(Page $page): Page
    {
        $this->children->add($page);

        if ($page->getParent() !== $this) {
            $page->setParent($this);
        }

        return $this;
    }

    public function removeChild(Page $page): Page
    {
        $this->children->removeElement($page);

        return $this;
    }

    public function isHomepage(): bool
    {
        return $this->homepage;
    }

    public function setHomepage(bool $homepage): Page
    {
        $this->homepage = $homepage;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host = null): Page
    {
        $this->host = $host;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale = null): Page
    {
        $this->locale = $locale;

        return $this;
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

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateSlug(): void
    {
        if (!$this->slug) {
            $this->slug = Transliterator::transliterate($this->title);
        }
    }

    /**
     * @ORM\PreRemove()
     */
    public function onRemove(LifecycleEventArgs $event): void
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
        $this->title .= '-'.$this->getId().'-deleted';
        $this->slug .= '-'.$this->getId().'-deleted';
    }
}
