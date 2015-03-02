<?php
/*
* This file is part of the PierstovalCmsBundle package.
*
* (c) Alexandre "Pierstoval" Rock Ancelet <pierstoval@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Pierstoval\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\IpTraceable\Traits\IpTraceableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Pierstoval\Bundle\CmsBundle\Repository\PageRepository")
 * @ORM\Table(name="pierstoval_cms_pages")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\TranslationEntity(class="Pierstoval\Bundle\CmsBundle\Entity\PageTranslation")
 * @UniqueEntity("slug")
 * @ORM\HasLifecycleCallbacks()
 */
class Page
{

    use SoftDeleteableEntity;
    use TimestampableEntity;
    use BlameableEntity;
    use IpTraceableEntity;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Gedmo\Translatable()
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Assert\Length(max=255)
     */
    protected $slug;

    /**
     * @var string
     * @Gedmo\Translatable()
     * @ORM\Column(name="page_content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var string
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected $metaDescription;

    /**
     * @var string
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    protected $metaTitle;

    /**
     * @var string
     * @ORM\Column(name="meta_keywords", type="string", length=255, nullable=true)
     */
    protected $metaKeywords;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Pierstoval\Bundle\CmsBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id", nullable=true)
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
     * @ORM\Column(name="host", type="string", length=255)
     */
    protected $host = '';

    /**
     * @var Page
     * @ORM\ManyToOne(targetEntity="Pierstoval\Bundle\CmsBundle\Entity\Page", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $parent;

    /**
     * @var Page[]
     * @ORM\OneToMany(targetEntity="Pierstoval\Bundle\CmsBundle\Entity\Page", mappedBy="parent")
     */
    protected $children;

    /**
     * @var PageTranslation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="PageTranslation", mappedBy="object", cascade={"persist", "remove"})
     */
    private $translations;

    public function __toString()
    {
        return $this->title;
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
    public function setTitle($title)
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
    public function setSlug($slug)
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
    public function setContent($content)
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
    public function setMetaDescription($metaDescription)
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
    public function setMetaTitle($metaTitle)
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
    public function setMetaKeywords($metaKeywords)
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
    public function setCategory($category)
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
    public function setCss($css)
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
    public function setJs($js)
    {
        $this->js = $js;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     *
     * @return Page
     */
    public function setEnabled($enabled)
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
     * @param Page $parent
     *
     * @return Page
     */
    public function setParent(Page $parent = null)
    {
        if ($parent && $parent->getId() == $this->id) {
            // Refuse the page to have itself as parent
            $this->parent = null;
            return $this;
        }
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Page[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Page[] $children
     *
     * @return Page
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHomepage()
    {
        return $this->homepage;
    }

    /**
     * @param boolean $homepage
     *
     * @return Page
     */
    public function setHomepage($homepage)
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
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return PageTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param PageTranslation $t
     * @return Page
     */
    public function addTranslation(PageTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
        return $this;
    }

    public function getTree($separator = '/')
    {
        $tree = '';

        $current = $this;
        do {
            $tree = $current->getSlug().$separator.$tree;
            $current = $current->getParent();
        } while ($current);

        return trim($tree, $separator);
    }

    /**
     * @ORM\PreRemove()
     * @param LifecycleEventArgs $event
     */
    public function onRemove(LifecycleEventArgs $event)
    {
        $om = $event->getObjectManager();
        foreach ($this->translations as $translation) {
            $om->remove($translation);
        }
        $om->flush();
        foreach ($this->children as $child) {
            $child->setParent(null);
            $om->persist($child);
        }
        $this->enabled = false;
        $this->parent = null;
        $this->title .= '-'.$this->id.'-deleted';
        $this->slug .= '-'.$this->id.'-deleted';
        $om->persist($this);
        $om->flush();
    }

}
