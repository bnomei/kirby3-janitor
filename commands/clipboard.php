<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Pipe `data` to `clipboard` arg in Janitor',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && exec('echo "'.$cli->arg('data').'" | pbcopy');
        defined('STDOUT') && $cli->success('Copied "'.$cli->arg('data').'" to your clipboard.');

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            // anything you forward to janitor in `clipboard` will be copied in panel.
            'clipboard' => $cli->arg('data'),
        ]);
    }
];
