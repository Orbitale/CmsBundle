
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dcb6d7ad-83c6-458d-acd6-8dde8b8020bc/mini.png)](https://insight.sensiolabs.com/projects/dcb6d7ad-83c6-458d-acd6-8dde8b8020bc)
[![Coverage Status](https://coveralls.io/repos/Orbitale/CmsBundle/badge.svg?branch=master)](https://coveralls.io/r/Orbitale/CmsBundle?branch=master)
[![Build Status](https://travis-ci.org/Orbitale/CmsBundle.svg?branch=master)](https://travis-ci.org/Orbitale/CmsBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Orbitale/CmsBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Orbitale/CmsBundle/?branch=master)

:warning: You're looking at the 3.x branch documentation.<br>
If you need information about 2.x, go [here](https://github.com/Orbitale/CmsBundle/tree/2.x)
If you need information about 1.x, go [here](https://github.com/Orbitale/CmsBundle/tree/1.x)

##### Index

* [Requirements](#requirements)
* [Install](#install)
* [Setup](#setup)
* [Usage](#usage)
 * [Manage pages](#manage-pages)
 * [View pages](#view-pages)
 * [Generate a route based on a single page](#generate-a-route-based-on-a-single-page)
* [Change homepage](#change-homepage)
* [Page restriction based `host` and/or `locale`](#page-restriction)
* [Design](#design)
 * [Using different layouts](#using-different-layouts)
 * [Advanced layout configuration](#advanced-layout-configuration)
 * [Changing the "breadcrumbs" look](#breadcrumbs)
* [Setup EasyAdminBundle to manage pages and categories in its back-end](#easyadmin)
* [Configuration reference](#configuration-reference)
* [Changelog](#changelog)

# Orbitale CMS Bundle

This bundle is a simple helper to create a very simple CMS based on a classic system with Pages and Categories.

## Requirements

* PHP 7.0+
* Symfony 3.0+
* Doctrine ORM

## Install

Require the bundle by using [Composer](https://getcomposer.org/):

```shell
$ composer require orbitale/cms-bundle
```

## Setup

Register the necessary bundles in your Kernel:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new Orbitale\Bundle\CmsBundle\OrbitaleCmsBundle(),
    ];
}

```

### Import the necessary routing files.

*Warning:*
 Both `Page` and `Category` controllers have to be "alone" in their routing path,
 because there is a "tree" routing management. If you set the prefix as "/"
 or any other path you are already using, make sure that `OrbitaleCmsBundle`
 routes are loaded **at the end of your routing file**, or you may have some
 unexpected "404" or other errors, depending on the routes priority.<br>
 This is why we recommend you to **load the `CategoryController` _before_ the
 `PageController`**, and let both routes config be **the last ones** of your
 `routing.yml` file.<br>
 **Note:** In technical terms, the whole URI is scanned, not a simple part of it,
 this is why it can analyze very deep URIs like
 `/home/blog/parent-link/child-link/element`, and check all pages/categories.

Example:

```yml
# app/config/routing.yml
orbitale_cms_category:
    resource: "@OrbitaleCmsBundle/Resources/config/routing/categories.yaml"
    prefix:   /category/
    
orbitale_cms_page:
    resource: "@OrbitaleCmsBundle/Resources/config/routing/pages.yaml"
    prefix:   /page/
```

### Create your entities

This bundle supports Doctrine ORM only.

In order to use it, you must create your own entities and configure the bundle with them.

Update your config:

```yml
# app/config/config.yml
orbitale_cms:
    page_class: AppBundle\Entity\Page
    category_class: AppBundle\Entity\Category
```

#### Create the `Page` entity and add it to your config

```php
<?php

namespace AppBundle\Entity;

use Orbitale\Bundle\CmsBundle\Entity\Page as BasePage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Orbitale\Bundle\CmsBundle\Repository\PageRepository")
 * @ORM\Table(name="orbitale_cms_pages")
 */
class Page extends BasePage
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
```

#### Create the `Category` entity and add it to your config

```php
<?php

namespace AppBundle\Entity;

use Orbitale\Bundle\CmsBundle\Entity\Category as BaseCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Orbitale\Bundle\CmsBundle\Repository\CategoryRepository")
 * @ORM\Table(name="orbitale_cms_categories")
 */
class Category extends BaseCategory
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

```

### Update your db schema

Update your database by executing this command from your Symfony root directory:

```bash
$ php app/console doctrine:schema:update --force
```

## Usage

### Manage pages

To manage your pages, you should use any back-end solution, like
[EasyAdmin](https://github.com/javiereguiluz/EasyAdminBundle/) (which we suggest)
or [SonataAdmin](https://sonata-project.org/bundles/admin), or any other backend
solution that can operate CRUD actions on entities.
You must have configured it to manage at least the
`Orbitale\Bundle\CmsBundle\Entity\Page` entity.

### View pages

The `PageController` handles some methods to view pages with a single
`indexAction()`, and the `CategoryController` uses its route to show all pages
within a specified `Category`.

The URI for both is simply `/{slug}` where `slug` is the... page or category slug.

If your page or category has one `parent`, then the URI is the following:
`/{parentSlug}/{slug}`.

You can notice that we respect the pages hierarchy in the generated url.

You can navigate through a complex list of pages or categories, as long as they
are related as `parent` and `child`.

This allows you to have such urls like this one :
`http://www.mysite.com/about/company/team/members` for instance, will show only
the `members` page, but its parent has a parent, that has a parent, and so on,
until you reach the "root" parent. And it's the same behavior for categories.

*Note:* this behavior is the precise reason why you have to use a specific rules
for your routing, unless you may have many "404" errors.

### Generate a route based on a single page

*Note:* This behavior also works for categories.

If you have a `Page` object in a view or in a controller, you can get the whole
arborescence by using the `getTree()` method, which will navigate through all
parents and return a string based on a separator argument (default `/`, for urls).

Let's get an example with this kind of tree:

```
/ - Home (root url)
├─ /welcome       - Welcome page (set as "homepage", so "Home" will be the same)
│  ├─ /welcome/our-company            - Our company
│  ├─ /welcome/our-company/financial  - Financial
│  └─ /welcome/our-company/team       - Team
└─ Contact
```

Imagine we want to generate the url for the "Team" page. You have this `Page`
object in your view/controller.

```twig
    {# Page : "Team" #}
    {{ path('orbitale_cms_page', {"slugs": page.tree}) }}
    {# Will show : /welcome/our-company/team #}
```

Or in a controller:

```php
    // Page : "Team"
    $url = $this->generateUrl('orbitale_cms_page', ['slugs' => $page->getTree()]);
    // $url === /welcome/our-company/team
```

With this, you have a functional tree system for your CMS!

## Change homepage

The homepage is always the first `Page` object with its `homepage` attribute set
to true. Be sure to have only one element defined as homepage, or you may have
unexpected results.

You can have multiple homepages if you add them restrictions based on host and
locale (see [next chapter](#page-restriction)).

## <a name="page-restriction"></a>Page restriction based `host` and/or `locale`

If you are hosting your application in a multi-domain platform, you can use the
`host` attribute in your page to restrict the view only to the specified host.

It's great for example if you want to have different articles on different
domains like `blog.mydomain.com` and `www.mydomain.com`.

If you want to restrict by `locale`, you can specify the locale in the page.
The best conjointed use is with prefixed routes in the routing file:

```yml
# app/config/routing.yml
orbitale_cms_page:
    resource: "@OrbitaleCmsBundle/Controller/PageController.php"
    type:     annotation
    # Add the locale to the prefix for if the page's locale is specified and is
    # not equal to request locale, the app will return a 404 error.
    prefix:   /{_locale}/page/
```

## Design

`OrbitaleCmsBundle` has some options to customize the design of your simple CMS.

Mostly, you will take care of the `layouts` option (see
[next chapter](#using-different-layouts)), or the `design` option.

### Using different layouts

Obviously, the default layout has no style.

To change the layout, simply change the OrbitaleCmsBundle configuration to add
your own layout:

```yaml
# app/config/config.yml
orbitale_cms:
    layouts:
        front: { resource: @App/layout.html.twig } # The Twig path to your layout
```

Without overriding anything, you can easily change the layout for your CMS!

Take a look at the [default layout](Resources/views/default_layout.html.twig)
to see which Twig blocks are mandatory to render correctly the pages.

### Advanced layout configuration

The basic configuration for a layout is to specify a template to extend.

But if you look at the [Configuration reference](#configuration-reference) you
 will see that there are many other parameters you can use to define a layout:

Prototype of a layout configuration:
* **name** (attribute used as key for the layouts list):<br>
 The name of your layout. Simply for readability issues, and maybe to get it
 directly from the config (if you need it).
* **resource**:<br>
 The Twig template used to render all the pages (see the [above](#using-different-layouts) section)
* **assets_css** and *assets_js*:<br>
 Any asset to send to the Twig `asset()` function. The CSS is rendered in the
 `stylesheets` block, and js in the `javascripts` block.
* **host**:<br>
 The exact domain name you want the layout to match with.
* **pattern**:<br>
 The regexp of the path you want to match with for this layout.
 It's nice if you want to use a different layout for categories and pages. For
 example, you can specify a layout for the `^/page/` pattern, and another for
 `^/category/`.
 If you specify a very deep pattern, you can even change the layout for a single
 page!

Take another look on the [config reference](#configuration-reference) if you
need to get the prototype defaults.

:warning: **Warning!** The **first matching** layout will be used, as well as
 routing would do, so be sure to configure them in the right order!<br>
Empty values won't be taken in account.

### <a name="breadcrumbs"></a>Changing the "breadcrumbs" look

Under the `design` option, you have some that you can use to optimize the
rendering of the breadcrumbs.

Basically, it will look like this:

[Homepage](#breadcrumbs) > [Parent page](#breadcrumbs) > Current page

**Note:** The breadcrumbs wrapper already has `id="breadcrumbs"` on its tag.

* **breadcrumbs_class**:<br>
  Changes the class of the breadcrumbs wrapper.
* **breadcrumbs_link_class**:<br>
  Changes the class of any link in the breadcrumbs.
* **breadcrumbs_current_class**:<br>
  Changes the class of the current page in the breadcrumbs (which is not a link).
* **breadcrumbs_separator** *(default: ">")*:<br>
  Changes the default separator used. You can use anything, but mostly we see `>`,
  `/`, `|` or `*` on the web.<br>
  **Note:** This character is escaped in twig, so do not use things like `&larr;`
  or the `&` sign will be replaced with `&amp;` (as well as other characters).
* **breadcrumbs_separator_class**:<br>
  You can specify a class for the separator (which is wrapped by a `<span>` tag),
  if you want to use special design or interaction on it.

## Cache

If you want to cache your cms results, just activate it via the config:

```yml
    cache:
        enabled: true
        ttl: 300
```

It uses Doctrine Result Cache so you need to activate it:

```yml
    doctrine:
        orm:
            result_cache_driver: apcu
```

You can read more about DoctrineCache <a href="http://symfony.com/doc/current/reference/configuration/doctrine.html#caching-drivers">here</a>.

## <a name="easyadmin"></a>Setup EasyAdminBundle to manage pages and categories in its back-end

This configuration allows you to manage your pages and categories directly in the [EasyAdminBundle](https://github.com/javiereguiluz/EasyAdminBundle) way.

First, install `EasyAdminBundle`, and set it up by reading its documentation (view link above).

After you installed it, you can add this configuration to inject your new classes in EasyAdmin:

```yml
# app/config/config.yml
easy_admin:
    entities:
        Pages:
            label: admin.cms.pages
            class: Orbitale\Bundle\CmsBundle\Entity\Page
            show:
                fields: [ id, parent, title, slug, tree, content, metaDescription, metaTitle, category, host, locale, homepage, enabled ]
            list:
                fields: [ id, parent, title, slug, tree, host, locale, { property: homepage, type: boolean }, { property: enabled, type: boolean } ]
            form:
                fields: [ title, slug, content, metaDescription, metaTitle, metaKeywords, css, js, category, parent, host, homepage, enabled ]

        Categories:
            label: "Cms Categories"
            class: Orbitale\Bundle\CmsBundle\Entity\Category
            show:
                fields: [ id, parent, title, slug, tree, content, host, locale, homepage, enabled ]
            list:
                fields: [ id, parent, name, slug, description, { property: enabled, type: boolean } ]
            form:
                fields: [ name, slug, description, parent, enabled ]
```

## Configuration reference

```yml
# app/config/config.yml
orbitale_cms:
    page_class: ~              # Required, must extend Orbitale Page class
    category_class: ~          # Required, must extend Orbitale Category class
    layouts:
        # Prototype
        name:
            name:       ~      # Optional, it's automatically set from the key if it's a string
            resource:   ~      # Required, must be a valid twig template
            assets_css: []     # Injected with the `asset()` twig function
            assets_js:  []     # Injected with the `asset()` twig function
            pattern:    ~      # Regexp
            host:       ~      # Exact value
    design:
        breadcrumbs_class:           "breadcrumb"  # The default value automatically suits to Bootstrap
        breadcrumbs_link_class:      ""
        breadcrumbs_current_class:   ""
        breadcrumbs_separator:       ">"
        breadcrumbs_separator_class: "breadcrumb-separator"
    cache:
        enabled: false
        ttl: 300

```

## Changelog

Go to the [releases](https://github.com/Orbitale/CmsBundle/releases) page to see what are the changes between each
new version of Orbitale CmsBundle!
