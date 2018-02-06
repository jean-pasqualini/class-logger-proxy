<?php

namespace Darkilliant\ClassLogger;

trait TraitLoggerProxy
{
    private static $_proxy_logger;

    public static function _proxy_setLogger($logger)
    {
        self::$_proxy_logger = $logger;
    }

    public function _proxy_dumpScalar($scalar)
    {
        if (is_array($scalar)) {
            return 'array';
        }

        if (is_object($scalar)) {
            return 'object='.get_class($scalar).'';
        }

        if (is_null($scalar)) {
            return 'NULL';
        }

        return gettype($scalar).'='.$scalar;
    }

    public function _proxy_preCall($name, $arguments)
    {
        $args = array_map([$this, '_proxy_dumpScalar'], $arguments);
        $method = $name.'('.implode($args, ', ').')';

        self::$_proxy_logger->log('INFO.class', '{class}::{method}', [
            '{class}' => get_class($this),
            '{method}' => $method,
        ]);
    }

    public function _proxy_postCall($name, $arguments, $returnValue)
    {
        $args = array_map([$this, '_proxy_dumpScalar'], $arguments);
        $method = $name.'('.implode($args, ', ').')';

        self::$_proxy_logger->log('INFO.class', '{class}::{method} '.PHP_EOL."\t > {return}", [
            '{class}' => get_class($this),
            '{method}' => $method,
            '{return}' => $this->_proxy_dumpScalar($returnValue),
        ]);
    }
}