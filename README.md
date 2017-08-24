# Sparhandy Coding Standard

This repository contains all necessary files to configure PHP Code Sniffer and PHP Mess Detector according to our coding standard. 

## Installation with composer

For now you have to use the dev-master version.

```bash
composer require "sparhandy/codingstandard" "dev-master"
```

## Usage

### From Terminal (Cli)

To execute PHP Code Sniffer configured for sparhandy coding standards do the following.
```bash
./vendor/bin/phpcs --extensions=php --standard=./vendor/sparhandy/codingstandard/src/phpcs/Production/ruleset.xml ./path/to ./your/sources 
```

To execute PHP Code Sniffer configured for sparhandy coding standards do the following.
```bash
./vendor/bin/phpmd ./path/to ./your/sources text ./vendor/sparhandy/codingstandard/src/phpmd/phpmd.xml --suffixes php
```

### Travis-CI configuration

For use in your Travis-Ci configuration file just adapt the following example and save it to .travis.yml in your root directory.
```
language: php

php:
  - 5.4
  - 5.5
  - 5.6
  - 7.0

matrix:
  allow_failures:
    - php: 7.0

before_script:
  - composer self-update

install: travis_retry composer update

script:
  - ./vendor/bin/phpmd ./path/to ./your/sources text ./configuration/phpmd/phpmd.xml --suffixes php
  - ./vendor/bin/phpcs --extensions=php --standard=./configuration/phpcs/Production/ruleset.xml ./path/to ./your/sources
```

# How to contribute

If you want to contribute to the standard here is how it works.

* Create a fork of Sparhandy/CodingStandard.
* Create your branch from master and commit your changes.
* Push your branch to your fork.
* Create a pull request on GitHub.
* Discuss your pull request with us.
* Our devs will then merge or close the PR.

# Acknowledgements
This would not be possible without the tremendous work of the good people at [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) and [PHP Mess Detector](https://github.com/phpmd/phpmd) and of cause the authors annotated in the source files.

