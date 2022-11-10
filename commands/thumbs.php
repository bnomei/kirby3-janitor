<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Generate all thumbs',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {

    }
];
