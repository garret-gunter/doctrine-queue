###### This is a work in progress and I'm still working out kinks.

Doctrine Queue
============

<!-- 

todo: update tags
todo: update README



[![Latest Stable Version](https://poser.pugx.org/gino-pane/composer-package-template/v/stable)](https://packagist.org/packages/gino-pane/composer-package-template)
[![License](https://poser.pugx.org/gino-pane/composer-package-template/license)](https://packagist.org/packages/gino-pane/composer-package-template)
[![composer.lock](https://poser.pugx.org/gino-pane/composer-package-template/composerlock)](https://packagist.org/packages/gino-pane/composer-package-template)
[![Total Downloads](https://poser.pugx.org/gino-pane/composer-package-template/downloads)](https://packagist.org/packages/gino-pane/composer-package-template)

-->

This is a fork of [SafeQueue](https://github.com/maxbrokman/SafeQueue) originally created by [maxbrokman](https://github.com/maxbrokman).  This is updated for Laravel 6.0.

Requirements
------------

* PHP >= 7.2
* Laravel: ^6.0
* laravel-doctrine/orm: ^1.5

<!-- 
Features
--------

* PSR-4 autoloading compliant structure;
* PSR-2 compliant code style;
* Unit-Testing with PHPUnit 6;
* Comprehensive guide and tutorial;
* Easy to use with any framework or even a plain php file;
* Useful tools for better code included.

Installation
============

    composer create-project gino-pane/composer-package-template yourproject
    
This will create a basic project structure for you:

* **/build** is used to store code coverage output by default;
* **/src** is where your codes will live in, each class will need to reside in its own file inside this folder;
* **/tests** each class that you write in src folder needs to be tested before it was even "included" into somewhere else. So basically we have tests classes there to test other classes;
* **.gitignore** there are certain files that we don't want to publish in Git, so we just add them to this fle for them to "get ignored by git";
* **CHANGELOG.md** to keep track of package updates;
* **CONTRIBUTION.md** Contributor Covenant Code of Conduct;
* **LICENSE** terms of how much freedom other programmers is allowed to use this library;
* **README.md** it is a mini documentation of the library, this is usually the "home page" of your repo if you published it on GitHub and Packagist;
* **composer.json** is where the information about your library is stored, like package name, author and dependencies;
* **phpunit.xml** It is a configuration file of PHPUnit, so that tests classes will be able to test the classes you've written;
* **.travis.yml** basic configuration for Travis CI with configured test coverage reporting for code climate.

Please refer to original [article](http://www.darwinbiler.com/creating-composer-package-library/) for more information.

Useful Tools
============

Running Tests:
--------

    php vendor/bin/phpunit
 
 or 
 
    composer test

Code Sniffer Tool:
------------------

    php vendor/bin/phpcs --standard=PSR2 src/
 
 or
 
    composer psr2check
-->

Changelog
=========

To keep track, please refer to [CHANGELOG.md](https://github.com/garret-gunter/doctrine-queue/blob/master/CHANGELOG.md).

<!--
Contributing
============

1. Fork it.
2. Create your feature branch (git checkout -b my-new-feature).
3. Make your changes.
4. Run the tests, adding new ones for your own code if necessary (phpunit).
5. Commit your changes (git commit -am 'Added some feature').
6. Push to the branch (git push origin my-new-feature).
7. Create new pull request.

Also please refer to [CONTRIBUTION.md](https://github.com/garret-gunter/doctrine-queue/blob/master/CONTRIBUTION.md).

-->

License
=======

Please refer to [LICENSE](https://github.com/garret-gunter/doctrine-queue/blob/master/LICENSE).
