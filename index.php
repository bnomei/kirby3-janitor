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
        'log.enabled' => false,
        'log.fn' => function (string $msg, string $level = 'info', array $context = []): bool {
            if (option('bnomei.janitor.log.enabled')) {
                if (function_exists('monolog')) {
                    monolog()->{$level}($msg, $context);
                } elseif (function_exists('kirbyLog')) {
                    kirbyLog('bnomei.janitor.log')->log($msg, $level, $context);
                }
                return true;
            }
            return false;
        },
    ],
    'snippets' => [
        'maintenance' => __DIR__ . '/snippets/maintenance.php',
    ],
    'commands' => [ // https://github.com/getkirby/cli
        'janitor:maintenance' => require __DIR__ . '/commands/maintenance.php',
        'janitor:tinker' => require __DIR__ . '/commands/tinker.php',
    ],
    'fields' => [
        'janitor' => [
            'props' => [
                'label' => function ($label = null) {
                    return \Kirby\Toolkit\I18n::translate($label, $label);
                },
                'progress' => function ($progress = null) {
                    return \Kirby\Toolkit\I18n::translate($progress, $progress);
                },
                'command' => function (?string $command = null) {
                    return 'plugin-janitor/' . str_replace(':','+C_O_L_O_N+', $command ?? '');
                },
                'cooldown' => function ($cooldownMilliseconds = null) {
                    return intval($cooldownMilliseconds ?? option('bnomei.janitor.label.cooldown'));
                },
                'data' => function (?string $data = null) {
                    $data = \Bnomei\Janitor::query($data, $this->model());
                    return str_replace(
                        '/',
                        '+S_L_A_S_H+',
                        \Kirby\Toolkit\I18n::translate($data, $data)
                    );
                },
                'clipboard' => function ($clipboard = null) {
                    return \Bnomei\Janitor::isTrue($clipboard);
                },
                'unsaved' => function ($allowUnsaved = true) {
                    return \Bnomei\Janitor::isTrue($allowUnsaved);
                },
                'autosave' => function ($doAutosave = false) {
                    return \Bnomei\Janitor::isTrue($doAutosave);
                },
                'intab' => function ($intab = false) {
                    return \Bnomei\Janitor::isTrue($intab);
                },
                'confirm' => function ($confirm = '') {
                    return $confirm;
                },
                'pageURI' => function () {
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
                'icon' => function ($icon = false) {
                    return $icon;
                },
            ],
        ],
    ],
    'routes' => [
        [
            'pattern' => 'plugin-janitor/(:any)/(:any)',
            'action' => function (string $job, string $secret) {
                $janitor = new \Bnomei\Janitor();
                $janitor->log('janitor-api-secret', 'debug');
                $response = $janitor->jobWithSecret($secret, $job);
                return Kirby\Http\Response::json($response, A::get($response, 'status', 400));
            },
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
                'action' => function (string $job, string $page, string $data) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job(str_replace('+C_O_L_O_N+', ':', $job), [
                        'page' => str_replace('+', '/', urldecode($page)),
                        'data' => str_replace('+S_L_A_S_H+', '/', urldecode($data)),
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)/(:any)',
                'action' => function (string $job, string $page) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job(str_replace('+C_O_L_O_N+', ':', $job), [
                        'page' => str_replace('+', '/', urldecode($page)),
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)',
                'action' => function (string $job) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job(str_replace('+C_O_L_O_N+', ':', $job));
                }
            ],
        ],
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
