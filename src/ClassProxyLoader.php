<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 10/02/18
 * Time: 15:08
 */

namespace Darkilliant\ClassLogger;


use Psr\Log\LoggerInterface;

class ClassProxyLoader
{
    private $path;
    private $logger;

    private $tobeDelete = [];

    public function __construct(string $path, LoggerInterface $logger)
    {
        $this->path = $path;
        $this->logger = $logger;
    }

    public function getProxyPath($class)
    {
        return $this->path.'/proxy_'.md5($class).'.php';
    }

    public function exists($class)
    {
        return file_exists($this->getProxyPath($class));
    }

    public function load($class, $originalFile)
    {
        $originalDirectory = pathinfo($originalFile, PATHINFO_DIRNAME);

        $proxyClassPath = $this->getProxyPath($class);
        $tmpProxyClassPath = $originalDirectory.'/.proxy_'.md5($class).'.php';

        if (file_exists($proxyClassPath)) {
            copy($proxyClassPath, $tmpProxyClassPath);
            require $tmpProxyClassPath;
            $this->tobeDelete[] = $tmpProxyClassPath;

            call_user_func_array([$class, '_proxy_setLogger'], [$this->logger]);
            return true;
        }

        return false;
    }

    public function dump($class, $content)
    {
        file_put_contents($this->getProxyPath($class), $content);
    }

    public function __destruct()
    {
        foreach ($this->tobeDelete as $toBeDeleteItem) {
            unlink($toBeDeleteItem);
        }
    }
}