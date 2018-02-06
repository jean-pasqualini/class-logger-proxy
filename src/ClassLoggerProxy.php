<?php

namespace Darkilliant\ClassLogger;

class ClassLoggerProxy
{
    /** @var Logger */
    private $logger;
    private $instance;

    public function __construct(Logger $logger, $instance)
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException('instance must be object');
        }

        $this->logger = $logger;
        $this->instance = $instance;
    }

    public function dumpScalar($scalar)
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

    public function __call($name, $arguments)
    {
        $args = array_map([$this, 'dumpScalar'], $arguments);
        $method = $name.'('.implode($args, ', ').')';

        $this->logger->log('INFO.class', '{class}::{method}', [
            '{class}' => get_class($this->instance),
            '{method}' => $method,
        ]);

        return call_user_func_array([$this->instance, $name], $arguments);
    }
}