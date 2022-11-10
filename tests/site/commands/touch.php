<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Touch a file',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        $file = site()->file($cli->arg('file'));
        touch($file->root());
        defined('STDOUT') && $cli->success($file->id() . ' => ' . $file->modified('c'));

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'label' => $file->modified('c'),
        ]);
    }
];
