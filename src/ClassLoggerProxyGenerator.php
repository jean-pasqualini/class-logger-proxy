<?php

namespace Darkilliant\ClassLogger;

use Composer\Autoload\ClassLoader;
use ReflectionParameter;
use Symfony\Component\Process\Process;

class ClassLoggerProxyGenerator
{
    private $classes = [];
    private $cacheDir;
    private $classLoader;
    private $tobeDelete = [];

    public function __construct(string $cacheDir, ClassLoader $classLoader)
    {
        $this->cacheDir = $cacheDir;
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->classLoader = $classLoader;
    }

    public function spy($class)
    {
        $this->classes[] = $class;
    }

    public function enable()
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    // Pk pas une v2 non pas bassé sur l'extension mais sur l'encapsulation du body des méthode dans une callback avec le même context $this
    private function buildProxy(\ReflectionClass $class): string
    {
        $data = [];

        $data['namespace'] = str_replace('Real', 'Proxy', $class->getNamespaceName());
        $data['namespace'] = str_replace('Proxy\\', '', $data['namespace']);

        $data['proxy_class'] = $class->getShortName();
        $data['real_class'] = '\\'.$class->getName();

        $data['methods'] = [];
        $reflMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($reflMethods as $reflMethod) {
            if ($reflMethod->isFinal() || $reflMethod->isStatic()) {
                continue;
            }

            $method = [];
            $method['name'] = $reflMethod->getName();
            $method['static'] = $reflMethod->isStatic();

            $parameters = [];
            $reflParameters = $reflMethod->getParameters();
            foreach ($reflParameters as $reflParameter) {
                $parameter = [
                    'type' => $reflParameter->getType(),
                    'name' => $reflParameter->getName(),
                ];
                if (!in_array($parameter['type'], ['array', 'callable', 'string', 'boolean', 'bool', 'integer', 'int', ''])) {
                    $parameter['type'] = '\\'.str_replace('Real\\', '', $parameter['type']);
                }
                if ($parameter['type'] != '') {
                    $parameter['type'] = $parameter['type'].' ';
                }

                $parameters[] = $parameter;
            }

            $method['parameters'] = $parameters;

            $body = file($class->getFileName());
            $method['body'] = implode(PHP_EOL."\t", array_slice($body, $reflMethod->getStartLine() + 1, ($reflMethod->getEndLine() - $reflMethod->getStartLine()) - 2));
            $method['start_line'] = $reflMethod->getStartLine() + 1;
            $method['end_line'] = $reflMethod->getEndLine() - 2;
            if ($method['body'] != "") {
                $data['methods'][] = $method;
            }
        }

//        dump($data);

        return $this->buildProxyFromMetadata($data, $class->getFileName());
    }

    private function buildProxyFromMetadata(array $data, string $file): string
    {
        $phpContent = file($file);

        // Find class
        foreach ($phpContent as $lineNumber => $line) {
            if (preg_match('/class [a-zA-Z+]/i', $line)) {
                $phpContent[$lineNumber+2] = "\t".'use \Darkilliant\ClassLogger\TraitLoggerProxy;'.PHP_EOL.$phpContent[$lineNumber+2];
                break;
            }
        }

        foreach ($data['methods'] as $method)
        {
            $process = new Process('php '.__DIR__.'/template/proxy.php \''.base64_encode(json_encode($method)).'\'');
            $process->run();

            $methodBody = $process->getOutput();

            for ($i = $method['start_line']; $i <= $method['end_line']; $i++) {
                $phpContent[$i] = '';
            }

            $phpContent[$method['start_line']] = $methodBody.PHP_EOL;
        }

        return str_replace('Real\\', '', implode('', $phpContent));
    }

    public function loadClass($class)
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

        if (!in_array($class, $this->classes)) {
            return;
        }

        $file = $this->classLoader->findFile($class);
        $realDirectory = pathinfo($file, PATHINFO_DIRNAME);

        $proxyClassPath = $this->cacheDir.'/proxy_'.md5($class).'.php';
        $tmpProxyClassPath = $realDirectory.'/.proxy_'.md5($class).'.php';
        $realClassPath = $this->cacheDir.'/real_'.md5($class).'.php';
        $tmpRealClassPath = $realDirectory.'/.real_'.md5($class).'.php';

        if (file_exists($proxyClassPath)) {

            copy($proxyClassPath, $tmpProxyClassPath);
            require $tmpProxyClassPath;
            $this->tobeDelete[] = $tmpProxyClassPath;

            call_user_func_array([$class, '_proxy_setLogger'], [new Logger()]);
            return;
        }

        $realClassContent = file_get_contents($file);
        $realClassName = 'Real\\'.$class;
        $realClassContent = preg_replace('/namespace ([a-zA-Z]+)/i', 'namespace Real\\\$1', $realClassContent);

        file_put_contents($realClassPath, $realClassContent);
        require $realClassPath;

        $proxyClassContent = $this->buildProxy(new \ReflectionClass($realClassName));
        file_put_contents($proxyClassPath, $proxyClassContent);
        require $proxyClassPath;
        call_user_func_array([$class, '_proxy_setLogger'], [new Logger()]);
        unlink($realClassPath);
    }

    public function __destruct()
    {
        foreach ($this->tobeDelete as $toBeDeleteItem) {
            unlink($toBeDeleteItem);
        }
    }
}