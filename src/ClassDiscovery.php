<?php

namespace Darkilliant\ClassLogger;

class ClassDiscovery
{
    private $discoveryForClasses;

    public function discovery($class, $recursive = false)
    {
        $this->discoveryForClasses[] = [
            'class' => substr($class, strrpos($class, '\\') + 1),
            'recursive' => $recursive,
        ];
    }

    // Article (search interface) -> not found
    // Abstract (search interface) -> found
    // Interface (search interface, abstract) -> not found
    public function isCoverByDiscovery($class, $file)
    {
        if (!$this->discoveryForClasses) {
            return false;
        }

        $fileContent = file_get_contents($file);
        $strategy = [];

        foreach ($this->discoveryForClasses as $discoveryForClass) {
            $strategy[] = ['pattern' => 'implements '.$discoveryForClass['class'], 'config' => $discoveryForClass];
            $strategy[] = ['pattern' => 'extends '.$discoveryForClass['class'], 'config' => $discoveryForClass];
            $strategy[] = ['pattern' => ', '.$discoveryForClass['class'], 'config' => $discoveryForClass];
        }

        foreach ($strategy as $strategyItem) {
            if (isset($strategyItem['pattern']) && strpos($fileContent, $strategyItem['pattern'])) {
                if ($strategyItem['config']['recursive']) {
                    $this->discovery($class, true);
                }

                return true;
            }
        }

        return false;
    }
}