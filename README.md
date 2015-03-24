[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dcb6d7ad-83c6-458d-acd6-8dde8b8020bc/mini.png)](https://insight.sensiolabs.com/projects/dcb6d7ad-83c6-458d-acd6-8dde8b8020bc)
[![Coverage Status](https://coveralls.io/repos/Orbitale/CmsBundle/badge.svg?branch=master)](https://coveralls.io/r/Orbitale/CmsBundle?branch=master)
[![Build Status](https://travis-ci.org/Orbitale/CmsBundle.svg?branch=master)](https://travis-ci.org/Orbitale/CmsBundle)

# Orbitale CMS Bundle

This bundle is a simple helper to create a very simple CMS based on a classic system with Pages and Categories.

## Requirements

* PHP 5.4+ (because we are using Traits)
* Symfony 2.3+
* Doctrine ORM

## Install

**Warning: This bundle uses PHP Traits, so you must have PHP 5.4+**

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
    $bundles = array(
        // ...
        new Orbitale\Bundle\CmsBundle\OrbitaleCmsBundle(),
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
    );
}

```

Import the necessary routing files:

```yml
# app/config/routing.yml

# Front-office, it has to be "alone" in its path, because there is a deep routing management.
# If you set the prefix as "/" or any other route you are already using, you may have some
#  unexpected "404" or other errors, depending on the routes priority.
orbitale_cms_front:
    resource: "@OrbitaleCmsBundle/Controller/FrontController.php"
    type:     annotation
    prefix:   /site/

```

## <a name="doctrine_extensions"></a> Setup Doctrine extensions

In order to work properly, this bundle uses the power of `GedmoDoctrineExtensions`, provided by @stof.
You have to add some configuration parameters in order to have it working:

```yml
# app/config/config.yml

services:
    gedmo.listener.timestampable:
        class: Gedmo\Timestampable\TimestampableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ @annotations.cached_reader ] ]

    gedmo.listener.sluggable:
        class: Gedmo\Sluggable\SluggableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ @annotations.cached_reader ] ]
```

## Usage

### Manage pages 

To manage your page, you should use any back-end solution, like [EasyAdmin](https://github.com/javiereguiluz/EasyAdminBundle/)
(which we suggest) or [SonataAdmin](https://sonata-project.org/bundles/admin), or any else.
You must have configured it to manage at least the `Orbitale\Bundle\CmsBundle\Entity\Page` entity.

### View pages

The `FrontController` handles some methods to view pages with a single `indexAction()`.

The URI for a classic page is simply `/{slug}` where `slug` is the... page slug.

If your page has one `parent`, then the URI is the following: `/{parentSlug}/{slug}`. As the slugs are verbose enough,
you can notice that we respect the pages hierarchy in the generated url.
You can navigate through a complex list of pages, as long as they're related as `parent` and `child`.
This allows you to have such urls like this one :
`http://www.mysite.com/about/company/team/members` for instance, will show only the `members` page, but its parent has
a parent, that has a parent, and so on, until you reach the "root" parent.
** Note: this behavior is the precise reason why you have to use a specific prefix for your `FrontController` routing
import, unless you may have many "404" errors.**

### Generate a route based on a single page

If you have a `Page` object in a view or in a controller, you can get the whole arborescence by using the `getTree()`
method, which will navigate through all parents and return a string based on a separator argument (default `/`, for urls).

Let's get an example with this kind of tree:

```
/ - Home (root url)
├─ /welcome                   - Welcome page (set as "homepage", so "Home" will be the same)
│  ├─ /welcome/our-company            - Our company
│  ├─ /welcome/our-company/financial  - Financial
│  └─ /welcome/our-company/team       - Team
└─ Contact
```

Imagine we want to generate the url for the "Team" page. You have this `Page` object in your view/controller.

```twig
    {# Page : "Team" #}
    {{ path('cms_home', {"slugs": page.tree}) }}
    {# Will show : /welcome/our-company/team #}
```

Or in a controller:

```php
    // Page : "Team"
    $url = $this->generateUrl('cms_home', array('slugs' => $page->getTree()));
    // $url === /welcome/our-company/team
```

With this, you have a functional tree system for your CMS!

## <a name="easyadmin"></a> Setup EasyAdminBundle to manage pages and categories in its back-end

**Note: you need to `composer requier javiereguiluz/easyadmin-bundle` to use the component.**

This configuration allows you to manage your pages and categories directly in the [EasyAdminBundle](https://github.com/javiereguiluz/EasyAdminBundle) way.

```yml
easy_admin:
    entities:
        "Cms Pages":
            class: Orbitale\Bundle\CmsBundle\Entity\Page
            list:
                fields: [ id, parent, title, slug, content, category, enabled ]
            form:
                fields: [ title, slug, content, metaDescription, metaTitle, metaKeywords, css, js, category, parent, enabled ]

        "Cms Categories":
            class: Orbitale\Bundle\CmsBundle\Entity\Category
            list:
                fields: [ id, parent, title, slug, content, category, enabled ]
            form:
                fields: [ title, slug, content, metaDescription, metaTitle, metaKeywords, css, js, category, parent, enabled ]
```

## <a name="homepage"></a> Customize home page

The homepage is always the first `Page` object with its `homepage` attribute set to true. Be sure to have only one
element defined as homepage, or you may have unexpected results.
