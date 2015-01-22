# Pierstoval CMS Bundle

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
        new Sonata\MediaBundle\SonataMediaBundle(),
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

Import the routing file:
```yml
# app/config/routing.yml
pierstoval_cms:
    resource: "@PierstovalCmsBundle/Resources/config/routing.yml"
