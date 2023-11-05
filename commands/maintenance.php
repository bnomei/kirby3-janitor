<?php

declare(strict_types=1);

if (! class_exists('Bnomei\Janitor')) {
    require_once __DIR__.'/../classes/Janitor.php';
}

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Maintenance',
    'args' => [
        'up' => [
            'longPrefix' => 'up',
            'description' => 'Up',
            'noValue' => true,
        ],
        'down' => [
            'longPrefix' => 'down',
            'description' => 'Down',
            'noValue' => true,
        ],
    ] + Janitor::ARGS,
    'command' => static function (CLI $cli): void {
        $message = '';
        $maintenance = kirby()->roots()->index().'/.maintenance';
        $down = ! file_exists($maintenance); // toggle
        if ($cli->arg('up')) {
            $down = false;
        }
        if ($cli->arg('down')) {
            $down = true;
        }

        if ($down === false) {
            file_exists($maintenance) && unlink($maintenance);
            $message = t('janitor.maintenance.up', 'online');
            $cli->success($message);
        } elseif ($down === true) {
            file_put_contents($maintenance, date('c').' ['.$cli->arg('user').']');
            $message = t('janitor.maintenance.down', 'in maintenance');
            $cli->red($message);
        }

        janitor()->data($cli->arg('command'), [
            'status' => $down ? 203 : 200,
            // urls forwarded to janitor in `download` will trigger a download in panel.
            'message' => $message,
            'label' => t(
                'janitor.maintenance.label',
                str_replace(
                    '{{ status }}',
                    site()->isUnderMaintenance()->ecco(
                        t('janitor.maintenance.down', 'in maintenance'),
                        t('janitor.maintenance.up', 'online')
                    ),
                    'Website is {{ status }}'
                )
            ),
            'icon' => $down ? 'cancel' : 'circle',
        ]);
    },
];
