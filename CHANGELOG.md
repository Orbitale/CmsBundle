#v4.1.0

* Make the project compatible with Symfony 6.0
* Test multiple versions of PHP and Symfony in CI
* **Potential BC break**: change minimum required Symfony versions to 5.3 instead of 5.0 (please upgrade 😉).

# v4.0.1

* Make the project compatible with Symfony 5.4
* Make the project compatible with PHP up to 8.1

# v4.0.0

### Breaking changes

* All service defintions now use class names instead of old-styled names. You might have to change your Dependency Injection if you inject CmsBundle's services by their name.
* Added default validation constraints for Page and Category entities. This might break your app if you pass Page or Category entities through Symfony Forms.
* Removed `Category::$createdAt`, didn't really make sense.
* Replaced `DateTime` with `DateTimeImmutable` in the `Page` entity.

### Other changes:

* Symfony 5.0 is now required
* PHP 7.3+ is required
* Added a PostsController to use Page objects like blog posts with date in URL (#25)
* Use Symfony/String instead of Behat/Transliterator as a slugger
* Made setters accept null as argument, for flexibility with how the Form component usually works

# v3.1.1

Fix Symfony deprecated controller notation

# v3.1.0

* Fix twig deprecation
* Migrate to newer versions of Symfony, Doctrine and Twig dependencies

# v3.0.6

* Fix Symfony 4 compatibility by making repositories services public

# v3.0.5

* Fix *all* issues with "lowest" packages versions (mostly causing doctrine bugs)
* Fixed phpunit damn issue with global vars snapshot

# v3.0.4

* Fix issue with twig loader

# v3.0.3

* Fixed an issue after last change that broke with Twig dependency

# v3.0.2

* Fix missing `templating` service issue

# v3.0.1

### Fixes

* Fixed page repo & breadcrumbs issue #9, #10 
* Add missing setter for CreatedAt

### Minor adjustments

* Searching categories is now done only if they're enabled
* Add "id" to category search fields
* Remove dependency with templating
* Changed twig service class depenency for layouts

# v3.0.0

* Compatible only with Symfony 3.0+
* Compatible only with PHP7+
* Move routing to files instead of annotations
* Remove useless dependencies & fragment Symfony dependencies instead of requiring the whole framework
* Refactored Travis config
* Updated Readme and license
* Renamed files with yaml instead of yml, to adapt to upcoming SF4 consistency with yaml file extension

# v2.0.5
Just updated the readme & license

# v2.0.4

* Fix issues with SF3.3 & refactor travis build

# v1.6.4
Just updated the readme & license

# v1.6.3

* Fix issues with SF3.3 & refactor travis build as of master

# v1.6.2

-Fixed named arguments for the layouts service

# v2.0.3

-Fixed named arguments for the layouts service

# v2.0.2

Mostly changes that are about tests, no feature or fix.

- Added a new test for non-functional class names and update travis CI
- Fix minimum PHP requirement in travis-CI
- Try to fix some tests caused by kernel test
- Remove lowest deps in travis
- Upgrade phpunit version in travis until v3

# v2.0.1

- Fix some readme issues
- Fix bidirectionnal issues

# v2.0.0

### Major changes and BC breaks

- Now, you must create your own `Page` and `Category` entities and configure them in the bundle, which is much better when you need to override them.
- Remove Gedmo doctrine behaviors: you have to implement them yourself if you need them.
- You can use your own repositories, but Orbitale's controllers use a specific one configured as a service. By the way, your repos can extend this one if you want.
- Make the bundle require php 5.4 (hey, we're in 2016!)

# v2.0.0

- Make config more strict & light for injections
- Remove `createdAt` setters and fix category slug update
- Fixed tests
- Make the bundle require php 5.4 (hey, we're in 2016!)
- Update docs to add warning about `1.x` and `master` branches

# v1.6.1

- Fix tag parameter injection (closes #7)

# v2.0.0

Now, you must create your own `Page` and `Category` entities, which is much better when you need to override them.
- Remove entities Gedmo dependencies and make them abstract
- Update config for new Page and Category classes
- Remove Gedmo services from configuration
- Create a listener to update mapped superclasses mapping (else it'd be "illegal" according to doctrine)
- Remove `updatedAt` sortable field in Category controller
- Update repositories so they use the correct classes
- Updated tests
- Fixed dependencies
- Update readme

# v1.6.0

- Make doctrine connection configurable under the `orbitale_cms.connection` parameter

# v1.5.1

### Fixes

- Remove yml deprecations in services.yml
- Redirect homepage with locale if has one only

### Minor adjustments

- Remove dead code
- Remove whitespaces in readme
- Fix tests
- Update dependencies and CI
- Update LICENSE date

# v1.5.0

### New features

- Add Doctrine Cache support for the bundle (@sfarkas1988)

# v1.4.1

- Some minor fixes, but now the bundle is compatible with Symfony3 ! :tada: 

# v1.4.0

In order to allow this bundle to migrate to Symfony 2.8, it now requires `twig/twig: ~1.23` because of the new `Twig_Extension_GlobalsInterface` class that must be extended by any extension requiring the use of `getGlobals()`.

This induces a small BC break for all apps requiring Twig with an inferior version.

- Minor update for Symfony 2.8 and 3.0

# v1.3.3

### Fixes

- Fix reversed breadcrumbs on page index

### Tests & CI

- Fixed PageControllerTest
- Fix travis-ci config to build only master

# v1.3.2

### Code style

- Minor change in code style (mostly for SensioLabsInsight & ScrutinizerCI)

# v1.3.1

- Fix bug in test after new default breadcrumb class in config

