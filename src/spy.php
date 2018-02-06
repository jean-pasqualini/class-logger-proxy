<?php

namespace App;

use AppBundle\Command\ArticleCreatorCommand;
use AppBundle\Entity\Article;
use Doctrine\ORM\EntityManager;
use Main\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;

$classLoggerProxyGenerator = new ClassLoggerProxyGenerator(__DIR__.'/../cache', $classLoader);
$classLoggerProxyGenerator->enable();
//$classLoggerProxyGenerator->spy(EntityManager::class);
//$classLoggerProxyGenerator->spy(ExceptionListener::class);
//$classLoggerProxyGenerator->spy(Request::class);
//$classLoggerProxyGenerator->spy(AppKernel::class);
//$classLoggerProxyGenerator->spy(FrameworkBundle::class);
//$classLoggerProxyGenerator->spy(EntityManager::class);
$classLoggerProxyGenerator->spy(Article::class);