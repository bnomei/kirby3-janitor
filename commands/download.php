<?php

declare(strict_types=1);

if (! class_exists('Bnomei\Janitor')) {
    require_once __DIR__.'/../classes/Janitor.php';
}

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Pipe `data` to `download` arg in Janitor or download on CLI via wget',
    'args' => [
        'output' => [
            'prefix' => 'o',
            'longPrefix' => 'output',
            'description' => 'output filename',
            'defaultValue' => '',
            'castTo' => 'string',
        ],
    ] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $cli->success('download => '.$cli->arg('data'));

        if (defined('STDOUT')) {
            $command = 'wget';
            if (! empty($cli->arg('output'))) {
                $command .= ' -O '.$cli->arg('output');
            }
            exec($command.' '.$cli->arg('data'));
        }

        janitor()->data($cli->arg('command'), [
            'status' => 200,
            // urls forwarded to janitor in `download` will trigger a download in panel.
            'download' => $cli->arg('data'),
        ]);
    },
];
