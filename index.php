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
        'jobs-defaults' => [
            'clean' => 'Bnomei\\CleanCacheFilesJob', // legacy
            'cleanCache' => 'Bnomei\\CleanCacheFilesJob',
            'flush' => 'Bnomei\\FlushPagesCacheJob', // legacy
            'flushPages' => 'Bnomei\\FlushPagesCacheJob',
            'cleanSessions' => 'Bnomei\\CleanSessionsJob',
            'flushSessions' => 'Bnomei\\FlushSessionFilesJob',
            'flushLapse' => 'Bnomei\\FlushLapseJob',
            'flushRedisDB' => 'Bnomei\\FlushRedisDBJob',
            'reindexAutoID' => 'Bnomei\\ReindexAutoIDJob',
            'reindexSearch' => 'Bnomei\\ReindexSearchForKirbyJob',
            'backupZip' => 'Bnomei\\BackupZipJob',
            'render' => 'Bnomei\\RenderJob',
            'thumbs' => 'Bnomei\\ThumbsJob',
        ],
        'jobs-extends' => [
            'bnomei.lapse.jobs', // https://github.com/bnomei/kirby3-lapse/blob/master/index.php#L10
        ],

        'label.cooldown' => 2000, // ms
        'secret' => null,

        'thumbsOnUpload' => false,

        'log.enabled' => false,
        'log.fn' => function (string $msg, string $level = 'info', array $context = []): bool {
            if (option('bnomei.janitor.log.enabled')) {
                if (function_exists('monolog')) {
                    monolog()->{$level}($msg, $context);
                }
                else if (function_exists('kirbyLog')) {
                    kirbyLog('bnomei.janitor.log')->log($msg, $level, $context);
                }
                return true;
            }
            return false;
        },
        'icon' => false,
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
                    return str_replace('/','+S_L_A_S_H+',
                        \Kirby\Toolkit\I18n::translate($data, $data)
                    );
                },
                'clipboard' => function ($clipboard = null) {
                    return \Bnomei\Janitor::isTrue($clipboard);
                },
                'unsaved' => function ($allowUnsaved = true) {
                    return \Bnomei\Janitor::isTrue($allowUnsaved);
                },
                'intab' => function ($intab = false) {
                    return \Bnomei\Janitor::isTrue($intab);
                },
                'pageURI' => function () {
                    $uri = null;
                    if (is_a($this->model(), \Kirby\Cms\Page::class)) {
                        $uri = $this->model()->uri();
                    }
                    if (is_a($this->model(), \Kirby\Cms\File::class)) {
                        $uri = $this->model()->parent()->uri();
                    }
                    return str_replace('/','+', $uri);
                },
                'icon' => function ($icon = false) {
                    return $icon ?? option('bnomei.janitor.icon');
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
        'file.create:after' => function ($file) {
            if (option('bnomei.janitor.thumbsOnUpload') && $file->isResizable()) {
                janitor('render', $file->page(), 'page');
                janitor('thumbs', $file->page(), 'page');
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
    function janitor(string $job, ?\Kirby\Cms\Page $contextPage = null, ?string $contextData = null, bool $dump = false)
    {
        $janitor = \Bnomei\Janitor::singleton();
        $janitor->log('janitor()', 'debug');
        $response = $janitor->job($job, [
            'contextPage' => $contextPage ? urlencode(str_replace('/' ,'+', $contextPage->uri())) : '',
            'contextData' => $contextData ? urlencode($contextData) : '',
        ]);
        if ($dump) {
            return $response;
        }
        return intval(A::get($response, 'status')) === 200;
    }
}
