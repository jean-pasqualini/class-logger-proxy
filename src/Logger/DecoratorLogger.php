<?php

namespace Darkilliant\ClassLogger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DecoratorLogger extends AbstractLogger
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}