<?php

namespace App;

use App\Entity\Article;
use Darkilliant\ClassLogger\ClassLogger;
use Darkilliant\ClassLogger\Logger\StdLogger;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/vendor/autoload.php';

// When spy abstract, spy auto all usage of public methods by inherited
//

$logger = new Logger('app');
$logger->pushProcessor(new PsrLogMessageProcessor());
$logger->pushHandler(new ChromePHPHandler());

$classLoggerProxyGenerator = new ClassLogger(__DIR__.'/../../cache', __DIR__.'/vendor/autoload.php');
$classLoggerProxyGenerator->setLogger($logger);
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spy(Article::class, true);

$article = new Article();
$article->setId(1000);
$article->setName('une maison blanche');
$article->getName();
$article->getUuid();

echo 'Hello world';
