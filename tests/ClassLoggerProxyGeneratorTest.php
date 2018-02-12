<?php

namespace Tests;

use Darkilliant\ClassLogger\ClassLoggerProxyGenerator;
use PHPUnit\Framework\TestCase;

class ClassLoggerProxyGeneratorTest extends TestCase
{
    /** @var ClassLoggerProxyGenerator */
    private $generator;

    public function setUp()
    {
        $this->generator = new ClassLoggerProxyGenerator();
    }

    public function testBuildProxyFromMetadata()
    {
        // 12, 14 ou 13
        $content = $this->generator->buildProxyFromMetadata([
            'methods' => [
                [
                    'name' => 'getId',
                    'start_line' => 13,
                    'end_line' => 13,
                    'parameters' => [],
                    'body' => '    return $this->id;'.PHP_EOL,
                ],
                [
                    'name' => 'setId',
                    'start_line' => 21,
                    'end_line' => 21,
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer '],
                    ],
                    'body' => '    $this->id = $id;'.PHP_EOL,
                ],
            ]
        ], __DIR__.'/fixtures/original_class/Article.php');

        self::assertEquals(
            file_get_contents(__DIR__.'/fixtures/generated_class/Article.php'),
            $content
        );
    }
}