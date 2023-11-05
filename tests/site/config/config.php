<?php

return [
    'debug' => true,
    'languages' => true,

    'bnomei.janitor.secret' => 'e9fe51f94eadabf54',

    // janitor v2 job callback
    'some.key.to.task' => function ($model, $data = null) {
        return [
            'status' => 200,
            'message' => $model->uuid().' '.$data,
            'help' => 'new help from api',
        ];
    },
    'random.colors' => function ($model, $data = null) {
        return [
            'status' => 200,
            'color' => '#'.dechex(rand(0x000000, 0xFFFFFF)), // random hex color
            'backgroundColor' => '#'.dechex(rand(0x000000, 0xFFFFFF)), // random hex color
        ];
    },

    //    'bnomei.janitor.maintenance.check' => function() {
    //        return kirby()->users()->current()?->role()->isAdmin() !== true;
    //    },

    'hooks' => [
        'page.delete:before' => function (Kirby\Cms\Page $page, bool $force) {
            // do something before a page gets deleted
            undertaker($page);
        },
    ],

    'routes' => [
        [
            'pattern' => 'webhook/(:any)/(:any)',
            'action' => function ($secret, $command) {
                if ($secret != janitor()->option('secret')) {
                    \Kirby\Http\Header::status(401);
                    exit();
                }

                if ($command === 'backup') {
                    janitor()->command('janitor:backupzip --quiet');
                    $backup = janitor()->data('janitor:backupzip')['path'];
                    if (F::exists($backup)) {
                        \Kirby\Http\Header::download([
                            'mime' => F::mime($backup),
                            'name' => F::filename($backup),
                        ]);
                        readfile($backup);
                        exit(); // needed to make content type work
                    }
                }
            },
        ],
    ],
];
