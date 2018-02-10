<?php

namespace App;

use App\Entity\AbstractEntityObject;
use App\Entity\Article;
use App\Entity\EntityObjectInterface;
use Darkilliant\ClassLogger\ClassLogger;
use Darkilliant\ClassLogger\ClassLoggerProxyGenerator;

$classLoader = require_once __DIR__.'/../../vendor/autoload.php';
$appClassLoader = require_once __DIR__.'/vendor/autoload.php';

// When spy abstract, spy auto all usage of public methods by inherited
//

$classLoggerProxyGenerator = new ClassLogger(__DIR__.'/../../cache', $appClassLoader);
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spyByDiscovery(AbstractEntityObject::class, true);

$article = new Article();
$article->setId(1000);
$article->setName('une maison blanche');
$article->getName();

