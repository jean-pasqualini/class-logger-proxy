<?php

namespace Darkilliant\ClassLogger;

class ClassReflector
{
    private function getReflectionClassByFile($class, $file)
    {
        $realClassPath = '/tmp/real_'.md5($class).'.php';

        $realClassContent = file_get_contents($file);
        $realClassName = 'Real\\'.$class;
        $realClassContent = preg_replace('/namespace ([a-zA-Z]+)/i', 'namespace Real\\\$1', $realClassContent);

        file_put_contents($realClassPath, $realClassContent);
        require $realClassPath;

        return new \ReflectionClass($realClassName);
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
        $methods = $this->getClassMethods($class->getName());
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

    private function getClassMethods($class)
    {
        $allMethods = get_class_methods($class);

        $classParent = get_parent_class($class);

        $methodsInherited = ($classParent) ? get_class_methods($classParent) : [];

        $methods = [];

        foreach ($allMethods as $method) {
            if (!in_array($method, $methodsInherited)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}