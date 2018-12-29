<?php

/*
janitor [noun]
one who keeps the premises of a building (such as an apartment or
office) clean, tends the heating system, and makes minor repairs
*/

Kirby::plugin('bnomei/janitor', [
    'options' => [
        'jobs' => [],
        'jobs.defaults' => [
            'tend' => function() {
                return \Bnomei\Janitor::cacheRemoveUnusedFiles();
            },
            'clean' => function() {
                return \Bnomei\Janitor::cacheClearFolders();
            },
            'clear' => function() {
                return \Bnomei\Janitor::cacheClearFolders();
            },
            'flush' => function() {
                return \Bnomei\Janitor::cacheFlush();
            },
            'repair' => function() {
                return \Bnomei\Janitor::cacheRepair();
            }
        ],
        'jobs.extends' => [],
        'exclude' => ['bnomei/autoid', 'bnomei/fingerprint'],
        'label.cooldown' => 2000, // ms
        'secret' => 'null',
        'simulate' => false, 
        'log.enabled' => false,
        'log' => function(string $msg, string $level = 'info', array $context = []):bool {
            if(option('bnomei.janitor.log.enabled') && function_exists('kirbyLog')) {
                kirbyLog('bnomei.janitor.log')->log($msg, $level, $context);
                return true;
            }
            return false;
        },
    ],
    'fields' => [
        'janitor' => [
            'props' => [
                'label' => function (string $label = null) {
                    return $label;
                },
                'progress' => function (string $progress = null) {
                    return $progress;
                },
                'job' => function (string $job = null) {
                    return 'plugin-janitor/' . $job;
                },
                'cooldown' => function(int $cooldownMilliseconds = 2000) {
                    return intval(option('bnomei.janitor.label.cooldown', $cooldownMilliseconds));
                }
            ]
        ]
    ],
    'routes' => [
        [
            'pattern' => 'plugin-janitor/(:any)/(:any)',
            'action' => function(string $job, string $secret) {
                \Bnomei\Janitor::log('janitor-api-secret', 'debug');
                $api = \Bnomei\Janitor::api($job, true, $secret);
                return Kirby\Http\Response::json($api, $api['status']);
            }
        ]
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'plugin-janitor/(:any)',
                'action' => function(string $job) {
                    \Bnomei\Janitor::log('janitor-api-auth', 'debug');
                    $api = \Bnomei\Janitor::api($job);
                    return $api;
                }
            ]
        ]
    ]

]);

if (!class_exists('Bnomei\Janitor')) {
    require_once __DIR__ . '/classes/Janitor.php';
}

if(!function_exists('janitor')) {
    function janitor(string $job, bool $dump = false) {
        \Bnomei\Janitor::log('janitor()', 'debug');
        $api = \Bnomei\Janitor::api($job);
        if ($dump) {
            return $api;
        } else {
            return intval($api['status']) == 200;
        }
    }
}
