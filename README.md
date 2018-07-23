[![Build Status](https://travis-ci.org/PhilippWitzmann/CodingStandard.svg?branch=master)](https://travis-ci.org/PhilippWitzmann/CodingStandard) [![Github Releases](https://img.shields.io/github/downloads/PhilippWitzmann/CodingStandard/latest/total.svg)]() [![Release](https://img.shields.io/github/release/PhilippWitzmann/CodingStandard.svg)]() [![Packagist](https://img.shields.io/packagist/l/PhilippWitzmann/CodingStandard.svg)]()

# Coding Standard

This repository contains all necessary files to configure PHP Code Sniffer and PHP Mess Detector. 

## Installation with composer

For now you have to use the dev-master version.

```bash
composer require "philippwitzmann/codingstandard" "~2"
```

## Usage

### From Terminal (Cli)

#### PHP Code Sniffer

To execute PHP Code Sniffer do the following.
```bash
./vendor/bin/phpcs --extensions=php --standard=./vendor/philippwitzmann/codingstandard/src/phpcs/Production/ruleset.xml ./path/to ./your/sources 
```

#### PHP Mess Detector
To execute PHP Mess Detector do the following.
```bash
./vendor/bin/phpmd ./path/to ./your/sources text ./vendor/philippwitzmann/codingstandard/src/phpmd/phpmd.xml --suffixes php
```

#### PHPStan
To execute PHP tan do the following.
```bash
./vendor/bin/phpstan analyse ./path/to ./your/sources
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

* Create a fork of PhilippWitzmann/CodingStandard.
* Create your branch from master and commit your changes.
* Push your branch to your fork.
* Create a pull request on GitHub.
* Discuss your pull request with us.
* Our devs will then merge or close the pull request.

# Acknowledgements
This would not be possible without the tremendous work of the good people at [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) and [PHP Mess Detector](https://github.com/phpmd/phpmd) and of course the authors annotated in the source files.
Also check out the original version of this standard over at the amazing people from [Sparhandy](https://github.com/Sparhandy/CodingStandard)