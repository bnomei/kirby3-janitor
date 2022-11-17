<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Show uuid from model as message, set `data` arg to overwrite',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $uuid = null;

        // get uuid from model (using a callback with $model would have been easier)
        if (!empty($cli->arg('page'))) {
            $uuid = $cli->kirby()->page($cli->arg('page'))->uuid()->toString();
        }
        if (!empty($cli->arg('file'))) {
            $uuid = $cli->kirby()->file($cli->arg('file'))->uuid()->toString();
        }
        if (!empty($cli->arg('user'))) {
            $uuid = $cli->kirby()->user($cli->arg('user'))->uuid()->toString();
        }
        if ($cli->arg('site')) {
            $uuid = $cli->kirby()->site()->uuid()->toString();
        }
        // overwrite if defined explicitly
        if (!empty($cli->arg('data'))) {
            $uuid = $cli->arg('data');
        }

        defined('STDOUT') && $cli->success($uuid);

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'message' => $uuid,
        ]);
    }
];
