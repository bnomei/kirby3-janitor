<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Show `data` arg as message',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->success($cli->arg('data'));

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'message' => $cli->arg('data'),
        ]);
    }
];
