<?php

@include_once __DIR__ . '/vendor/autoload.php';

/*
janitor [noun]
one who keeps the premises of a building (such as an apartment or
office) clean, tends the heating system, and makes minor repairs
*/

Kirby::plugin('bnomei/janitor', [
    'options' => [
        'jobs' => [],
        'jobs.defaults' => [
            'clean' => 'Bnomei\\CleanCacheFilesJob',
            'flush' => 'Bnomei\\FlushPagesCacheJob',
        ],
        'jobs.extends' => [],

        'label.cooldown' => 2000, // ms
        'secret' => null,

        'log.enabled' => false,
        'log' => function (string $msg, string $level = 'info', array $context = []): bool {
            if (option('bnomei.janitor.log.enabled') && function_exists('kirbyLog')) {
                kirbyLog('bnomei.janitor.log')->log($msg, $level, $context);
                return true;
            }
            return false;
        },
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
                'job' => function (?string $job = null) {
                    return 'plugin-janitor/' . $job;
                },
                'cooldown' => function (int $cooldownMilliseconds = 2000) {
                    return intval(option('bnomei.janitor.label.cooldown', $cooldownMilliseconds));
                },
                'data' => function (?string $data = null) {
                    $data = \Bnomei\Janitor::query($data, $this->model());
                    return \Kirby\Toolkit\I18n::translate($data, $data);
                },
                'clipboard' => function ($clipboard = null) {
                    return \Bnomei\Janitor::isTrue($clipboard);
                },
                'pageURI' => function () {
                    return $this->model()->uri();
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
    'api' => [
        'routes' => [
            [
                'pattern' => 'plugin-janitor/(:any)/(:any)/(:any)',
                'action' => function (string $job, string $page, string $data) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job($job, [
                        'contextPage' => $page,
                        'contextData' => $data,
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)/(:any)',
                'action' => function (string $job, string $page) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job($job, [
                        'contextPage' => $page,
                    ]);
                },
            ],
            [
                'pattern' => 'plugin-janitor/(:any)',
                'action' => function (string $job) {
                    $janitor = \Bnomei\Janitor::singleton();
                    $janitor->log('janitor-api-auth', 'debug');
                    return $janitor->job($job);
                }
            ],
        ],
    ],
]);

if (!class_exists('Bnomei\Janitor')) {
    require_once __DIR__ . '/classes/Janitor.php';
}

if (!function_exists('janitor')) {
    function janitor(string $job, bool $dump = false)
    {
        $janitor = \Bnomei\Janitor::singleton();
        $janitor->log('janitor()', 'debug');
        $response = $janitor->job($job);
        if ($dump) {
            return $response;
        }
        return intval(A::get($response, 'status')) === 200;
    }
}
