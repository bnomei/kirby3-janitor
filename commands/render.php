<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Render all pages',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
    }
];
