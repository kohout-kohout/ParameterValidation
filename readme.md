Arachne/ParameterValidation
====

[![Build Status](https://img.shields.io/travis/Arachne/ParameterValidation/master.svg?style=flat-square)](https://travis-ci.org/Arachne/ParameterValidation/branches)
[![Coverage Status](https://img.shields.io/coveralls/Arachne/ParameterValidation/master.svg?style=flat-square)](https://coveralls.io/github/Arachne/ParameterValidation?branch=master)
[![Latest stable](https://img.shields.io/packagist/v/arachne/parameter-validation.svg?style=flat-square)](https://packagist.org/packages/arachne/parameter-validation)
[![Downloads this Month](https://img.shields.io/packagist/dm/arachne/parameter-validation.svg?style=flat-square)](https://packagist.org/packages/arachne/parameter-validation)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/Arachne/ParameterValidation/blob/master/license.md)

Installation
----

The best way to install Arachne/ParameterValidation is using [Composer](http://getcomposer.org/):

```sh
composer require arachne/parameter-validation
```

Now you need to register the extension using your [neon](https://ne-on.org) config file.

```neon
extensions:
    arachne.parameterValidation: Arachne\ParameterValidation\DI\ParameterValidationExtension
```
