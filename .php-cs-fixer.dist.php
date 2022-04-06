<?php


$finder = PhpCsFixer\Finder::create()
    ->exclude('content')
    ->exclude('kirby')
    ->exclude('node_modules')
    //->exclude('site/plugins')
    ->exclude('src')
    ->exclude('vendor')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder)
;
