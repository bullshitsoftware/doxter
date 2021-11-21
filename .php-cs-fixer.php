<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/migrations')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PHPUnit84Migration:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
