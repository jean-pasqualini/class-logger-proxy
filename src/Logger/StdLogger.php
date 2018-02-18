<?php

namespace Darkilliant\ClassLogger\Logger;

use Psr\Log\AbstractLogger;

class StdLogger extends AbstractLogger
{
    private $stream;

    public function __construct(string $stream)
    {
        $this->stream = fopen($stream, 'w+');
    }

    public function log($level, $message, array $context = array())
    {
        $replacements = array();
        foreach ($context as $key => $val) {
            $replacements['{'.$key.'}'] = "\033[0;36m".$val."\033[0m";;
        }

        $message = strtr($message, $replacements);

        fwrite(
            $this->stream,
            sprintf(
                    '%s [%s] : %s%s',
                    date('H:i:s'),
                    $level,
                    $message,
                    PHP_EOL
            )
        );
    }

    public function __destruct()
    {
        fclose($this->stream);
    }
}