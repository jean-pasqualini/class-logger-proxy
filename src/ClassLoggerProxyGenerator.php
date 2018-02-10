<?php

namespace Darkilliant\ClassLogger;

use Symfony\Component\Process\Process;

class ClassLoggerProxyGenerator
{
    public function buildProxyFromMetadata(array $data, string $file): string
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
}