<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 10/02/18
 * Time: 15:08
 */

namespace Darkilliant\ClassLogger;


class ClassProxyLoader
{
    private $path;

    private $tobeDelete = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getProxyPath($class)
    {
        return $this->path.'/proxy_'.md5($class).'.php';
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

            call_user_func_array([$class, '_proxy_setLogger'], [new Logger()]);
            return true;
        }

        return false;
    }

    public function __destruct()
    {
        foreach ($this->tobeDelete as $toBeDeleteItem) {
            unlink($toBeDeleteItem);
        }
    }
}