<?php

namespace Tests;

use App\FrameworkBundle;
use Darkilliant\ClassLogger\ClassReflector;
use PHPUnit\Framework\TestCase;

class ClassReflectorTest extends TestCase
{
    /**
     * @var ClassReflector
     */
    private $reflector;

    public function setUp()
    {
        $this->reflector = new ClassReflector();
    }

    public function testGetStructure()
    {
        require_once __DIR__.'/fixtures/original_class/Bundle.php';

        $structure = $this->reflector->getStructure(
            FrameworkBundle::class,
            __DIR__.'/fixtures/original_class/FrameworkBundle.php'
        );

        $this->assertEquals([
            'namespace' => 'App',
            'proxy_class' => 'FrameworkBundle',
            'real_class' => '\App\FrameworkBundle',
            'methods' => [
                [
                    'name' => 'boot',
                    'static' => false,
                    'parameters' => [],
                    'body' => "        echo 'hello';\n",
                    'start_line' => 8,
                    'end_line' => 8,
                ]
            ]
        ], $structure);
    }
}