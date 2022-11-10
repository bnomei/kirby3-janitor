<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Maintenance',
    'args' => [
        'up' => [
            'longPrefix'  => 'up',
            'description' => 'Up',
            'noValue'     => true,
        ],
        'down' => [
            'longPrefix'  => 'down',
            'description' => 'Down',
            'noValue'     => true,
        ],
    ] + Janitor::ARGS,
    'command' => static function (CLI $cli): void {
        $message = '';
        $maintenance = kirby()->roots()->index() . '/.maintenance';
        $down = !file_exists($maintenance); // toggle
        if ($cli->arg('up')) {
            $down = false;
        }
        if ($cli->arg('down')) {
            $down = true;
        }

        if ($down === false) {
            file_exists($maintenance) && unlink($maintenance);
            $message = 'UP ' . date('c') . ' [' . $cli->arg('user') . ']';
            defined('STDOUT') && $cli->success($message);
        }
        elseif ($down === true) {
            $message = 'DOWN ' . date('c') . ' [' . $cli->arg('user') . ']';
            file_put_contents($maintenance, $message);
            defined('STDOUT') && $cli->red($message);
        }

        janitor()->data($cli->arg('command'), [
            'status' => $down ? 203 : 200,
            // urls forwarded to janitor in `download` will trigger a download in panel.
            'label' => $message,
        ]);
    }
];
