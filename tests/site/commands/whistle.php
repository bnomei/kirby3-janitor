<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Whistle',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $cli->success(' ♫ ');

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'message' => ' ♫ ',
        ]);
    },
];
