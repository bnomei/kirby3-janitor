<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Pipe `data` to `download` arg in Janitor',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->success('download => ' . $cli->arg('data'));

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            // urls forwarded to janitor in `download` will trigger a download in panel.
            'download' => $cli->arg('data'),
        ]);
    }
];
