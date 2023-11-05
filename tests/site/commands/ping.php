<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Ping',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        sleep(1);
        $success = rand(0, 10) > 0; // 10% chance to fail
        (
            $success ? $cli->success('Pong') : $cli->error('BAMM')
        );

        janitor()->data($cli->arg('command'), [
            'status' => $success ? 200 : 404,
            // messages for success and error are defined in blueprint
            // but could also be provided here
            // 'success' => 'Pang',
            // 'error' => 'BONG',
        ]);
    },
];
