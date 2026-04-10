<?php

declare(strict_types=1);

require_once __DIR__ . '/RecipeBuilder.php';

use Ixopay\Tools\Recipes\RecipeBuilder;

$root = dirname(__DIR__, 2);
$source = $argv[1] ?? $root . '/tools/recipes/templates';
$target = $argv[2] ?? $root . '/docs/recipes/how-to';

$builder = new RecipeBuilder();
$builtFiles = $builder->buildDirectory($source, $target);

foreach ($builtFiles as $builtFile) {
    echo 'Built ' . str_replace($root . '/', '', $builtFile) . PHP_EOL;
}
