<?php

namespace Darkilliant\ClassLogger;

use Symfony\Component\Process\Process;

class ClassReflector
{
    private function getReflectionClassByFile($class, $file)
    {
        require $file;

        return new \ReflectionClass($class);
    }

    public function getIsolatedStructure($class, $file, $composerAutoloadFile)
    {
        $context = [
            'class' => $class,
            'file' => $file,
            'autoload' => $composerAutoloadFile,
        ];
        $process = new Process('php '.__DIR__.'/structure.php \''.base64_encode(json_encode($context)).'\'');
        $process->run();

        if (!$process->isSuccessful()) {
            echo $process->getErrorOutput();
        }

        $structure = json_decode($process->getOutput(), true);

        if (!$structure) {
            exit('analyse class problem');
        }

        return $structure;
    }

    public function getStructure($class, $file): array
    {
        $class = $this->getReflectionClassByFile($class, $file);

        $data = [];

        $data['namespace'] = str_replace('Real', 'Proxy', $class->getNamespaceName());
        $data['namespace'] = str_replace('Proxy\\', '', $data['namespace']);

        $data['proxy_class'] = $class->getShortName();
        $data['real_class'] = '\\'.$class->getName();

        $data['methods'] = [];
        $methods = $this->getClassMethods($class->getName(), $class->getTraits());
        foreach ($methods as $method) {
            $reflMethod = $class->getMethod($method);
            if (!$reflMethod->isPublic() || $reflMethod->isFinal() || $reflMethod->isStatic()) {
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

        return $data;
    }

    private function getClassMethods($class, array $traits)
    {
        $methods = [];

        $reflectionClass = new \ReflectionClass($class);
        $reflectionMethods = $reflectionClass->getMethods();

        foreach ($reflectionMethods as $reflectionMethod) {
            if (!$reflectionMethod->isPublic() || $reflectionMethod->isFinal() || $reflectionMethod->isStatic()) {
                continue;
            }
            if ($reflectionMethod->getDeclaringClass()->getName() === $class) {
                $methods[] = $reflectionMethod->getName();
            }
        }

        return $methods;
    }
}