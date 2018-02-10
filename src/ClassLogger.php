<?php

namespace Darkilliant\ClassLogger;

use Composer\Autoload\ClassLoader;

class ClassLogger
{
    private $classes = [];
    private $cacheDir;
    private $classLoader;
    private $classDiscovery;
    private $classReflector;
    private $generator;
    private $proxyLoader;

    public function __construct(string $cacheDir, ClassLoader $classLoader, ClassLoggerProxyGenerator $generator = null, ClassDiscovery $classDiscovery = null, ClassReflector $classReflector = null, ClassProxyLoader $proxyLoader = null)
    {
        $this->cacheDir = $cacheDir;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->classLoader = $classLoader;
        $this->classDiscovery = new ClassDiscovery();
        $this->classReflector = new ClassReflector();
        $this->generator = new ClassLoggerProxyGenerator();
        $this->proxyLoader = new ClassProxyLoader($this->cacheDir);
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

    public function fixClassLoadInRealNamespace($class)
    {
        // Tempory use case special for autoload in real namespace
        // Le problème c'est qu'une classe dans le namespace Real
        // Peut attendre en typehint
        // La version Real
        // Et une autre la version non Real (donc d'origine)
        // Peut être un class alias de la non real vers la
        // Sa marche mais l'inverse serait mieux car il éviterai un file_put_content
        if (strpos($class, 'Real') !== false) {
            $expectedClass = str_replace('Real\\', '', $class);
            $file = $this->classLoader->findFile($expectedClass);
            $expectedClassDirectory = pathinfo($file, PATHINFO_DIRNAME);

            $realClassContent = file_get_contents($file);
            $realClassName = 'Real\\'.$class;
            $realClassContent = str_replace('namespace ', 'namespace Real\\', $realClassContent);
            $realClassPath = $expectedClassDirectory.'/'.uniqid().'.php';

            file_put_contents($realClassPath, $realClassContent);
            require $realClassPath;
            unlink($realClassPath);
            //var_dump($expectedClass, $class);
            //exit();
            class_alias($class, $expectedClass);
        }
    }

    public function loadClass($class)
    {
        $this->fixClassLoadInRealNamespace($class);

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

        if (!$this->proxyLoader->load($class, $file)) {
            $structure = $this->classReflector->getStructure($class, $file);
            $proxyClassContent = $this->generator->buildProxyFromMetadata($structure, $file);
            file_put_contents($this->proxyLoader->getProxyPath($class), $proxyClassContent);
        }
    }
}