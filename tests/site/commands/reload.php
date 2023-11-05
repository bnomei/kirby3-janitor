
<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Reloads the page in the panel.',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $cli->error('No reload in CLI.');

        ray('reload', $cli->args());

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'reload' => true, // will trigger JS location.reload in panel
        ]);
    },
];
