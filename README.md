# Pierstoval CMS Bundle

This bundle is a simple helper to create a little CMS based on a
classic `User`, `Page`, and `Post` system.

It is using the awesome [EasyAdminBundle](https://github.com/javiereguiluz/EasyAdminBundle/) by [Javier Eguiluz](https://github.com/javiereguiluz)

### Install


Require the bundle in composer:
```shell
$ composer require pierstoval/cms-bundle
```

Setup the used bundles:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Pierstoval\Bundle\CmsBundle\PierstovalCmsBundle(),
        new Pierstoval\Bundle\TranslationBundle\PierstovalTranslationBundle(),
        new JavierEguiluz\Bundle\EasyAdminBundle\EasyAdminBundle(),
        new JMS\SerializerBundle\JMSSerializerBundle(),
        new FOS\UserBundle\FOSUserBundle(),
    );
}

```

Import the config:

```yml
# app/config/config.yml
imports:
    # ...
    - { resource: @PierstovalCmsBundle/Resources/config/config.yml }
```

The routing file contains some basic config for your CMS, such as `FOSUserBundle` and `EasyAdminBundle` routing, as well
as `CmsBundle` routing. You can import it if you have not already imported FOS and EasyAdmin routes:

```yml
# app/config/routing.yml
pierstoval_cms:
    resource: "@PierstovalCmsBundle/Resources/config/routing.yml"
```


The basic EasyAdmin configuration has to be added to your config:

```yml
# app/config/config.yml

easy_admin:
    entities:
        "Cms Pages":
            class: Pierstoval\Bundle\CmsBundle\Entity\Page
            list:
                fields: [ id, parent, title, slug, content, category, enabled ]
            form:
                fields: [ title, slug, content, metaDescription, metaTitle, metaKeywords, css, js, level, category, parent, enabled ]

```
