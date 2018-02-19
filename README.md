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
use Darkilliant\ClassLogger\Logger\StdLogger;

//...

$classLoggerProxyGenerator = new ClassLogger(__DIR__.'/../../cache', __DIR__.'/vendor/autoload.php');
$classLoggerProxyGenerator->setLogger(new StdLogger('php://stdout'));
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spy('FullQualitifiedClassName');
```

And use normalyse

#### Demo

##### Console line mode

```bash
$ git clone class-logger && cd class-logger
$ composer install
$ cd src/demo
$ composer install
$ php demo.php 
```

##### Web mode

```bash
$ git clone class-logger && cd class-logger
$ composer install
$ cd src/demo
$ composer install
$ php -S 0.0.0.0:80
```

1. Install extension chrome https://chrome.google.com/webstore/detail/chrome-logger/noaneddfkdjfnfdakjjmocngnfkfehhd
2. Click on the icon on chrome for active logger
3. Go to [demo web](http://localhost:8080/demo_web.php)

#### Qualité

![PhpUnit](https://travis-ci.org/jean-pasqualini/class-logger-proxy.svg?branch=master)