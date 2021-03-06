<?php

namespace Darkilliant\ClassLogger;

use Darkilliant\ClassLogger\Logger\DecoratorLogger;
use Psr\Log\LoggerInterface;

class ClassLogger
{
    private $classes = [];
    private $cacheDir;
    private $classLoader;
    private $classDiscovery;
    private $classReflector;
    private $generator;
    private $proxyLoader;
    private $autoloaderPath;
    private $spyLogger;

    public function __construct(string $cacheDir, string $autoloaderPath, ClassLoggerProxyGenerator $generator = null, ClassDiscovery $classDiscovery = null, ClassReflector $classReflector = null, ClassProxyLoader $proxyLoader = null, LoggerInterface $spyLogger = null)
    {
        $this->cacheDir = $cacheDir;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->autoloaderPath = $autoloaderPath;
        $this->spyLogger = new DecoratorLogger();
        $this->classLoader = require $autoloaderPath;
        $this->classDiscovery = new ClassDiscovery();
        $this->classReflector = new ClassReflector();
        $this->generator = new ClassLoggerProxyGenerator();
        $this->proxyLoader = new ClassProxyLoader($this->cacheDir, $this->spyLogger);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->spyLogger->setLogger($logger);
    }

    public function spy($class)
    {
        $this->classes[] = $class;
    }

    public function spyByDiscovery($class, $recursive = false)
    {
        $this->classDiscovery->discovery($class, $recursive);
    }

    public function enable()
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass($class)
    {
        $file = $this->classLoader->findFile($class);

        if (!$file) {
            return;
        }

        if ($this->classDiscovery->isCoverByDiscovery($class, $file)) {
            $this->classes[] = $class;
        }

        if (!in_array($class, $this->classes)) {
            return;
        }

        // When proxy not exists, generate
        if (!$this->proxyLoader->exists($class)) {
            $this->proxyLoader->dump(
                $class,
                $this->generator->buildProxyFromMetadata(
                    $this->classReflector->getIsolatedStructure($class, $file, $this->autoloaderPath),
                    $file
                )
            );
        }

        // Load proxy
        $this->proxyLoader->load($class, $file);
    }
}