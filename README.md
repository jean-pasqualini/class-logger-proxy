#### Description

Class logger spy interaction with class and show into log

Interaction contains,
- name of class
- name method called
- params passed
- return value

Log compatible with PSR3-Logger for usage,
- Console log into cli (example)
- ChromePHP handler
- Decorate logger for one logger available after

It's possible spy,
- Abstract class
- Final class
- Class

It's not possible spy,
- Trait (for the moment support 90%)
- Internal php class (futur support multiple driver php-aop maybe possible)

It's possible use discovery class with interface.

#### Installation

```bash
$ composer require darkilliant/class-logger
```

#### Usage

On entry point file add instructions,

```php
<?php

namespace App;

use Darkilliant\ClassLogger\ClassLogger;

//...

$classLoggerProxyGenerator = new ClassLogger(__DIR__.'/../../cache', __DIR__.'/vendor/autoload.php');
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spy('FullQualitifiedClassName');
```

And use normalyse

#### Demo

```bash
$ git clone class-logger && cd class-logger
$ composer install
$ cd src/demo
$ composer install
$ php demo.php 
```

#### Qualité

![PhpUnit](https://travis-ci.org/jean-pasqualini/class-logger-proxy.svg?branch=master)