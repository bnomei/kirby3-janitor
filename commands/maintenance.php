<?php

return [
    'description' => 'Maintenance',
    'args' => [
        'up' => [
            'prefix'      => 'u',
            'longPrefix'  => 'up',
            'description' => 'Up',
            'noValue'     => true,
        ],
        'down' => [
            'prefix'      => 'd',
            'longPrefix'  => 'down',
            'description' => 'Down',
            'noValue'     => true,
        ],
    ],
    'command' => static function ($cli): void {
        $maintenance = kirby()->roots()->index() . '/.maintenance';
        if ($cli->arg('up')) {
            if (file_exists($maintenance)) {
                unlink($maintenance);
            }
            $cli->success('UP');
        }
        if ($cli->arg('down')) {
            file_put_contents(kirby()->roots()->index() . '/.maintenance', (string) time());
            $cli->success('DOWN');
        }

        $cli->error('Missing argument `u`/`up` or `d`/`down`.');
    }
];
