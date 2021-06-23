<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'vendor',
    ])
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'ordered_imports' =>  ['sort_algorithm' => 'alpha'],
    ])
    ->setFinder($finder)
    ->setUsingCache(false);

