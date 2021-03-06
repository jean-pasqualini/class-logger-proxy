<?php

namespace App;

use App\Entity\AbstractEntityObject;
use App\Entity\Article;
use Darkilliant\ClassLogger\ClassLogger;
use App\Entity\UuidTrait;
use Darkilliant\ClassLogger\Logger\StdLogger;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/vendor/autoload.php';

// When spy abstract, spy auto all usage of public methods by inherited
//

$classLoggerProxyGenerator = new ClassLogger(__DIR__.'/../../cache', __DIR__.'/vendor/autoload.php');
$classLoggerProxyGenerator->setLogger(new StdLogger('php://stdout'));
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spy(Article::class, true);

$article = new Article();
$article->setId(1000);
$article->setName('une maison blanche');
$article->getName();
$article->getUuid();

