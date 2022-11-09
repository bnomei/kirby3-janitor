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
        'janitor:maintenance' => require __DIR__ . '/commands/maintenance.php',
        'janitor:tinker' => require __DIR__ . '/commands/tinker.php',
    ],
    'fields' => [
        'janitor' => [
            'props' => [
                'args' => function ($args = null) {
                    $args = \Bnomei\Janitor::query($args, $this->model());
                    return str_replace(
                        '/',
                        '+++',
                        \Kirby\Toolkit\I18n::translate($args, $args)
                    );
                },
                'autosave' => function ($doAutosave = false) {
                    return \Bnomei\Janitor::isTrue($doAutosave);
                },
                'clipboard' => function ($clipboard = null) {
                    return \Bnomei\Janitor::isTrue($clipboard);
                },
                'command' => function ($command = null) {
                    $command = \Bnomei\Janitor::query($command, $this->model());
                    return 'plugin-janitor/' . str_replace(':','+', $command ?? '');
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
                    return \Kirby\Toolkit\I18n::translate($label, $label);
                },
                'progress' => function ($progress = null) {
                    $progress = \Bnomei\Janitor::query($progress, $this->model());
                    return \Kirby\Toolkit\I18n::translate($progress, $progress);
                },
                'success' => function ($success = null) {
                    $success = \Bnomei\Janitor::query($success, $this->model());
                    return \Kirby\Toolkit\I18n::translate($success, $success);
                },
                'unsaved' => function ($allowUnsaved = true) {
                    return \Bnomei\Janitor::isTrue($allowUnsaved);
                },
                'uri' => function () {
                    $uri = kirby()->site()->homePageId();
                    if (is_a($this->model(), \Kirby\Cms\Page::class)) {
                        $uri = $this->model()->uri();
                    }
                    if (is_a($this->model(), \Kirby\Cms\File::class)) {
                        $uri = $this->model()->parent()->uri();
                    }
                    if (is_a($this->model(), \Kirby\Cms\User::class)) {
                        $uri = $this->model()->panel()->path();
                    }
                    if (is_a($this->model(), \Kirby\Cms\Site::class)) {
                        $uri = '$'; // any not empty string so route /$/DATA is used
                    }
                    return str_replace('/', '+', $uri);
                },
            ],
        ],
    ],
    'hooks' => [
        // maintenance
        'route:before' => function () {
            $isPanel = strpos(
                    kirby()->request()->url()->toString(),
                    kirby()->urls()->panel()
                ) !== false;
            $isApi = strpos(
                    kirby()->request()->url()->toString(),
                    kirby()->urls()->api()
                ) !== false;
            if (!$isPanel && !$isApi) {
                if (F::exists(kirby()->roots()->index() . '/.maintenance')) {
                    snippet('maintenance');
                    die;
                }
            }
        },
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'plugin-janitor/(:any)/(:any)/(:any)',
                'action' => function (string $command, string $page, string $data) {
                    $janitor = \Bnomei\Janitor::singleton();
                    return $janitor->job(str_replace('+', ':', $command), [
                        '--page', str_replace('+', '/', urldecode($page)),
                        ...explode(' ', str_replace('+++', '/', urldecode($data))),
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)/(:any)',
                'action' => function (string $command, string $page) {
                    $janitor = \Bnomei\Janitor::singleton();
                    return $janitor->job(str_replace('+', ':', $command), [
                        '--page', str_replace('+', '/', urldecode($page)),
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)',
                'action' => function (string $command) {
                    $janitor = \Bnomei\Janitor::singleton();
                    return $janitor->job(str_replace('+', ':', $command));
                }
            ],
        ],
    ],
    'routes' => [
        [
            'pattern' => 'plugin-janitor/(:any)/(:any)',
            'action' => function (string $command, string $secret) {
                $janitor = new \Bnomei\Janitor();
                $response = $janitor->jobWithSecret($secret, $command);
                return Kirby\Http\Response::json($response, A::get($response, 'status', 400));
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
    function janitor()
    {
        return \Bnomei\Janitor::singleton();
    }
}
