<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Call a Janitor v2 job closure',
    'args' => [
            'key' => [
                'longPrefix' => 'key',
                'description' => 'Key of option (not name of a class)',
                'required' => true,
            ],
        ] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        $key = $cli->arg('key');
        $job = option($key);
        $result = [];

        $model = null;
        if ($cli->arg('site')) {
            $model = $cli->kirby()->site();
        } elseif (!empty($cli->arg('page'))) {
            $model = $cli->kirby()->page($cli->arg('page'));
        } elseif (!empty($cli->arg('file'))) {
            $model = $cli->kirby()->file($cli->arg('file'));
        } elseif (!empty($cli->arg('user'))) {
            $model = $cli->kirby()->user($cli->arg('user'));
        }

        if ($model && !is_string($job) && is_callable($job)) {
            if (empty($cli->arg('data'))) {
                $result = $job($model);
            } else {
                $result = $job($model, $cli->arg('data'));
            }
        }
        janitor()->data($cli->arg('command'), $result);

        defined('STDOUT') && (A::get($result, 'status') === 200 ? $cli->success($key) : $cli->error($key));
        defined('STDOUT') && $cli->out(print_r($result, true));
    }
];
