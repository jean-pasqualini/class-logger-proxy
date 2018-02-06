<?php

namespace App;

use App\Entity\Article;
use Darkilliant\ClassLogger\ClassLoggerProxyGenerator;

$classLoader = require_once __DIR__.'/../../vendor/autoload.php';
$appClassLoader = require_once __DIR__.'/vendor/autoload.php';

$classLoggerProxyGenerator = new ClassLoggerProxyGenerator(__DIR__.'/../../cache', $appClassLoader);
$classLoggerProxyGenerator->enable();
$classLoggerProxyGenerator->spy(Article::class);

$article = new Article();
$article->setName('une maison blanche');
$article->getName();