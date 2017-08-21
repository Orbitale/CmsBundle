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
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(name="page_content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var string
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     */
    protected $metaDescription;

    /**
     * @var string
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    protected $metaTitle;

    /**
     * @var string
     * @ORM\Column(name="meta_keywords", type="string", length=255, nullable=true)
     */
    protected $metaKeywords;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var string
     * @ORM\Column(name="css", type="text", nullable=true)
     */
    protected $css;

    /**
     * @var string
     * @ORM\Column(name="js", type="text", nullable=true)
     */
    protected $js;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled = false;

    /**
     * @var bool
     * @ORM\Column(name="homepage", type="boolean")
     */
    protected $homepage = false;

    /**
     * @var string
     * @ORM\Column(name="host", type="string", length=255, nullable=true)
     */
    protected $host;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=6, nullable=true)
     */
    protected $locale;

    /**
     * @var Page
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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Page
     */
    public function setTitle(string $title): Page
    {
        $this->title = $title;

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
     * @return Page
     */
    public function setSlug(string $slug): Page
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Page
     */
    public function setContent(string $content = null): Page
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     *
     * @return Page
     */
    public function setMetaDescription(string $metaDescription = null): Page
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     *
     * @return Page
     */
    public function setMetaTitle(string $metaTitle = null): Page
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     *
     * @return Page
     */
    public function setMetaKeywords(string $metaKeywords = null): Page
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return Page
     */
    public function setCategory(Category $category = null): Page
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @param string $css
     *
     * @return Page
     */
    public function setCss(string $css = null): Page
    {
        $this->css = $css;

        return $this;
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return $this->js;
    }

    /**
     * @param string $js
     *
     * @return Page
     */
    public function setJs(string $js = null): Page
    {
        $this->js = $js;

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
     * @return Page
     */
    public function setCreatedAt(\DateTime $date): Page
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return Page
     */
    public function setEnabled(bool $enabled): Page
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Page
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Page|null $parent
     *
     * @return Page
     */
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

    /**
     * @param Page $page
     *
     * @return Page
     */
    public function addChild(Page $page): Page
    {
        $this->children->add($page);

        if ($page->getParent() !== $this) {
            $page->setParent($this);
        }

        return $this;
    }

    /**
     * @param Page $page
     *
     * @return Page
     */
    public function removeChild(Page $page): Page
    {
        $this->children->removeElement($page);

        return $this;
    }

    /**
     * @return bool
     */
    public function isHomepage(): bool
    {
        return $this->homepage;
    }

    /**
     * @param bool $homepage
     *
     * @return Page
     */
    public function setHomepage(bool $homepage): Page
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return Page
     */
    public function setHost(string $host = null): Page
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Page
     */
    public function setLocale(string $locale = null): Page
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $separator
     *
     * @return string
     */
    public function getTree(string $separator = '/')
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
            $this->slug = Transliterator::transliterate($this->title);
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
        $this->title .= '-'.$this->getId().'-deleted';
        $this->slug .= '-'.$this->getId().'-deleted';
    }
}
