
<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Ping',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        defined('STDOUT') && $cli->error('No reload in CLI.');

        janitor()->data($cli->arg('command'), [
            'status' => 200 ,
            'reload' => true, // will trigger JS location.reload in panel
        ]);
    }
];
