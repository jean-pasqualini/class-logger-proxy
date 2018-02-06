<?php

namespace Darkilliant\ClassLogger;

class Logger
{
    public function log($type, $message, array $context = [])
    {
        $params = array_keys($context);
        $values = array_values($context);
        foreach ($values as &$value) {
            $value = "\033[0;36m".$value."\033[0m";
        }

        $message = str_replace($params, $values, $message);

        echo sprintf(
                '%s [%s] : %s',
                date('H:i:s'),
                $type,
                $message
            ).PHP_EOL;
    }
}