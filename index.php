<?php

@include_once __DIR__ . '/vendor/autoload.php';

/*
janitor [noun]
one who keeps the premises of a building (such as an apartment or
office) clean, tends the heating system, and makes minor repairs
*/

Kirby::plugin('bnomei/janitor', [
    'options' => [
        'label.cooldown' => 2000, // ms
        'secret' => null,
    ],
    'commands' => [ // https://github.com/getkirby/cli
        'janitor:backupzip' => require __DIR__ . '/commands/backupzip.php',
        'janitor:cleancontent' => require __DIR__ . '/commands/cleancontent.php',
        'janitor:clipboard' => require __DIR__ . '/commands/clipboard.php',
        'janitor:download' => require __DIR__ . '/commands/download.php',
        'janitor:flush' => require __DIR__ . '/commands/flush.php',
        'janitor:maintenance' => require __DIR__ . '/commands/maintenance.php',
        'janitor:open' => require __DIR__ . '/commands/open.php',
        'janitor:pipe' => require __DIR__ . '/commands/pipe.php',
        'janitor:render' => require __DIR__ . '/commands/render.php',
        'janitor:thumbs' => require __DIR__ . '/commands/thumbs.php',
        'janitor:tinker' => require __DIR__ . '/commands/tinker.php',
    ],
    'fields' => [
        'janitor' => [
            'props' => [
                'autosave' => function ($doAutosave = false) {
                    return \Bnomei\Janitor::isTrue($doAutosave);
                },
                'clipboard' => function ($clipboard = null) {
                    return \Bnomei\Janitor::isTrue($clipboard);
                },
                'command' => function ($command = null) {
                    // resolve queries
                    $command = \Bnomei\Janitor::query($command, $this->model());
                    // append model
                    if ($this->model() instanceof \Kirby\Cms\Page) {
                        $command .= ' --page ' . $this->model()->uuid() ?? $this->model()->id();
                    } elseif ($this->model() instanceof \Kirby\Cms\File) {
                        $command .= ' --file ' . $this->model()->uuid() ?? $this->model()->id();
                    } elseif ($this->model() instanceof \Kirby\Cms\User) {
                        $command .= ' --user ' . $this->model()->uuid() ?? $this->model()->id();
                    } elseif ($this->model() instanceof \Kirby\Cms\Site) {
                        $command .= ' --site';
                    }
                    return $command;
                },
                'confirm' => function ($confirm = '') {
                    return $confirm;
                },
                'cooldown' => function ($cooldownMilliseconds = null) {
                    return intval($cooldownMilliseconds ?? option('bnomei.janitor.label.cooldown'));
                },
                'error' => function ($error = null) {
                    $error = \Bnomei\Janitor::query($error, $this->model());
                    return \Kirby\Toolkit\I18n::translate($error, $error);
                },
                'icon' => function ($icon = false) {
                    return $icon;
                },
                'intab' => function ($intab = false) {
                    return \Bnomei\Janitor::isTrue($intab);
                },
                'label' => function ($label = null) {
                    $label = \Bnomei\Janitor::query($label, $this->model());
                    return \Kirby\Toolkit\I18n::translate($label, $label);
                },
                'progress' => function ($label = null) {
                    $label = \Bnomei\Janitor::query($label, $this->model());
                    return \Kirby\Toolkit\I18n::translate($label, $label);
                },
                'success' => function ($label = null) {
                    $label = \Bnomei\Janitor::query($label, $this->model());
                    return \Kirby\Toolkit\I18n::translate($label, $label);
                },
                'unsaved' => function ($allowUnsaved = true) {
                    return \Bnomei\Janitor::isTrue($allowUnsaved);
                },
            ],
        ],
    ],
    'hooks' => [
        // maintenance
        'route:before' => function () {
            if (\Bnomei\Janitor::requestBlockedByMaintenance() &&
                F::exists(kirby()->roots()->index() . '/.maintenance')) {
                snippet('maintenance');
                die;
            }
        },
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'plugin-janitor/(:all)', // using (:all) fixes issues with kirbys routing for : and /
                'action' => function (string $command) {
                    return \Bnomei\Janitor::singleton()->job(urldecode($command));
                },
            ]
        ],
    ],
    'routes' => [
        [
            'pattern' => 'plugin-janitor/(:any)/(:all)', // using (:all) fixes issues with kirbys routing for : and /
            'action' => function (string $secret, string $command) {
                $janitor = \Bnomei\Janitor::singleton();
                if ($secret == $janitor->option('secret')) {
                    return $janitor->job(urldecode($command));
                }
                return [
                    'status' => 401,
                ];
            },
        ],
    ],
    'snippets' => [
        'maintenance' => __DIR__ . '/snippets/maintenance.php',
    ],
]);

if (!class_exists('Bnomei\Janitor')) {
    require_once __DIR__ . '/classes/Janitor.php';
}

if (!function_exists('janitor')) {
    function janitor(): \Bnomei\Janitor
    {
        return \Bnomei\Janitor::singleton();
    }
}
