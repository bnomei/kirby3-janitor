<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Remove all content data that has no blueprint setup',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {

    }
];
