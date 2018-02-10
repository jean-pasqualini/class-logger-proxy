<?php

namespace App;

use Darkilliant\ClassLogger\ClassReflector;

$context = json_decode(base64_decode($_SERVER['argv'][1]), true);
$context = ($context) ? $context : [];

require $context['autoload'];
require __DIR__.'/ClassReflector.php';

$classReflection = new ClassReflector();

$structure = $classReflection->getStructure($context['class'], $context['file']);

echo json_encode($structure);