<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Minimal',
    'args' => [] + Janitor::ARGS,
    'command' => static function (CLI $cli): void {
        janitor()->data($cli->arg('command'), [
            'status' => 200,
        ]);
    }
];
