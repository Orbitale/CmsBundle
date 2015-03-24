# Orbitale CMS Bundle

This bundle is a simple helper to create a little CMS based on a classic system.

It proposes to use the awesome [EasyAdminBundle](https://github.com/javiereguiluz/EasyAdminBundle/) by
[Javier Eguiluz](https://github.com/javiereguiluz) to generate a simple backoffice

## Index

* [Install](#install)
* [Setup](#setup)
* [Usage](#usage)
* [Set up `OrbitaleTranslationBundle` *(optional)*](#translation)
* [Setup Doctrine extensions *(optional)*](#doctrine_extensions)
* [Using FOSUserBundle to have a secured backoffice *(optional)*](#fosuserbundle)

## Requirements

* PHP 5.4+ (because we are using Traits)
* Doctrine ORM

## Install

**Warning: This bundle uses PHP Traits, so you must have PHP 5.4+**

Require the bundle in composer:

```shell
$ composer require orbitale/cms-bundle
```

## Setup 

Setup the necessary bundles:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Orbitale\Bundle\CmsBundle\OrbitaleCmsBundle(),
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),  // Optional
        new Orbitale\Bundle\TranslationBundle\OrbitaleTranslationBundle(), // Optional
        new JavierEguiluz\Bundle\EasyAdminBundle\EasyAdminBundle(),        // Optional
    );
}

```

Activate the translator (it's mandatory for doctrine extensions, and also if you use `OrbitaleTranslationBundle`)

```yml
# app/config/config.yml
framework:
    translator:      { fallback: %locale% }

## This configuration allows you to manage your pages and categories directly in the EasyAdminBundle way
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

Import the necessary routing files:

```yml
# app/config/routing.yml

# Front-office, it has to be "alone" in its path, because there is a deep routing management.
# If you set the prefix as "/" or any other route you are already using, you may have some
#  unexpected "404" or other errors.
orbitale_cms_front:
    resource: "@OrbitaleCmsBundle/Controller/FrontController.php"
    type:     annotation
    prefix:   /site/

# Admin panel, granted by @javiereguiluz's powerful EasyAdminBundle
easy_admin:
    resource: "@EasyAdminBundle/Controller"
    type: annotation
    prefix: /admin/
```

## <a name="translation"></a> Set up `OrbitaleTranslationBundle` *(optional)*

If you use [OrbitaleTranslationBundle](https://github.com/Orbitale/TranslationBundle) , you can configure your locales.
(of course you need to have registered the bundle in your `AppKernel`)

```yml
# app/config/config.yml
orbitale_translation:
    locales: fr,de,en,es # Add all locales you want to use in your application
```

## <a name="doctrine_extensions"></a> Setup Doctrine extensions

In order to work properly, this bundle uses the power of GedmoDoctrine Extensions, provided by @stof.
You have to add some configuration parameters in order to have it working properly:

```yml
# app/config/config.yml

doctrine:
    orm:
        mappings:
            translatable:
                is_bundle: false
                type: annotation
                alias: Gedmo
                prefix: Gedmo\Translatable\Entity
                dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity"

services:
    gedmo.listener.translatable:
        class: Gedmo\Translatable\TranslatableListener
        tags:
            - { name: doctrine.event_subscriber, connection: default }
        calls:
            - [ setAnnotationReader, [ @annotations.cached_reader ] ]
            - [ setDefaultLocale, [ %locale% ] ]
            - [ setTranslationFallback, [ false ] ]

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

Simply go to your backoffice in `/admin`, and login if you are using the `Security` component.

You can manage `Cms Pages` and `Cms Categories` as you wish, like in an usual backoffice.

The `FrontController` handles some methods to view pages with a single `indexAction()`.

The URI for a classic page is simply `/{slug}` where `slug` is the... page slug (wow, thanks captain hindsight!).

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

## <a name="fosuserbundle"></a> Using FOSUserBundle to have a secured backoffice

If want to use `FOSUserBundle`, you have to setup the bundle by reading [FOSUserBundle's documentation](https://github.com/FriendsOfSymfony/FOSUserBundle#documentation).

First, register the bundle in your kernel:

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(), // Only if you want a secured backoffice. See "Using FOSUserBundle" section below for more info.
    );
}

```

You can add these basic parameters if you want:

```yml
# app/config/routing.yml
fos_user_security:
    resource: "@FOSUserBundle/Resources/config/routing/security.xml"

fos_user_profile:
    resource: "@FOSUserBundle/Resources/config/routing/profile.xml"
    prefix: /profile

fos_user_register:
    resource: "@FOSUserBundle/Resources/config/routing/registration.xml"
    prefix: /register

fos_user_resetting:
    resource: "@FOSUserBundle/Resources/config/routing/resetting.xml"
    prefix: /resetting

fos_user_change_password:
    resource: "@FOSUserBundle/Resources/config/routing/change_password.xml"
    prefix: /profile
```

```yml
# app/config/config.yml
# FOSUser basic config
fos_user:
    db_driver:     orm
    firewall_name: main
    user_class:    AppBundle\Entity\User # Specify your own User class, depending on its namespace and where you created it.
```

For this, use the basic `Security` component, configure your firewall as you would always do.

If you do not know how-to, you can read the [Symfony documentation](http://symfony.com/fr/doc/current/book/security.html)

Basically, you could add this settings as an example in your `security.yml` file:

```yml
# app/config/security.yml
security:
    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: ROLE_ADMIN

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
        main:
            pattern: ^/
            form_login:
                provider: fos_userbundle
                csrf_provider: form.csrf_provider
            logout:       true
            anonymous:    true

    access_control:
        - { path: ^/login$,     role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/register,   role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/resetting,  role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/,     role: ROLE_ADMIN }
```

## <a name="homepage"></a> Customizing home page

The homepage is always the first `Page` object with its `homepage` attribute set to true. Be sure to have only one
element defined as homepage, or you may have unexpected results.
