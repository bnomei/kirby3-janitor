<?php

declare(strict_types=1);

if (! class_exists('Bnomei\Janitor')) {
    require_once __DIR__.'/../classes/Janitor.php';
}

use Bnomei\Janitor;
use Kirby\CLI\CLI;

/*
 * PANEL:
 * janitor:trash --name pages --key "home.en.html"
 * janitor:trash --name pages --page "page://vf0xqIlpU0ZlSorI"
 * janitor:trash --page "page://vf0xqIlpU0ZlSorI"
 * janitor:trash
 *
 * CLI:
 * env KIRBY_HOST=janitor.test vendor/bin/kirby janitor:trash --page "page://vf0xqIlpU0ZlSorI"
 */
return [
    'description' => 'Removes an entry from a cache',
    'args' => [
        'name' => [
            'prefix' => 'n',
            'longPrefix' => 'name',
            'description' => 'Name of the cache',
            'defaultValue' => 'pages',
        ],
        'key' => [
            'prefix' => 'k',
            'longPrefix' => 'key',
            'description' => 'Key for a cache entry',
            'required' => false,
        ],
    ] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $name = $cli->arg('name');
        $name = empty($name) ? 'pages' : $name;

        $page = $cli->arg('page');
        if ($page && $name === 'pages') {
            if ($page = $cli->kirby()->page($page)) {
                if ($cli->kirby()->multilang()) {
                    foreach ($cli->kirby()->languages() as $language) {
                        $cacheId = [$page->id(), $language->code()];
                        if ($name === 'pages') {
                            $cacheId[] = 'html'; // render content type
                        }
                        $cacheId = implode('.', $cacheId);
                        $cli->kirby()->cache($name)->remove($cacheId);
                        $cli->success('The entry "'.$cacheId.'" in the cache "'.$name.'" has been removed.');
                    }
                } else {
                    $cacheId = [$page->id()];

                    if ($name === 'pages') {
                        $cacheId[] = 'html'; // render content type
                    }

                    $cacheId = implode('.', $cacheId);
                    $cli->kirby()->cache($name)->remove($cacheId);
                    $cli->success('The entry "'.$cacheId.'" in the cache "'.$name.'" has been removed.');
                }

                janitor()->data($cli->arg('command'), [
                    'status' => 200,
                ]);

                return;
            }
        }

        // if it has a key just remove that
        if ($key = $cli->arg('key')) {
            $cli->kirby()->cache($name)->remove($key);
            $cli->success('The entry "'.$key.'" in the cache "'.$name.'" has been removed.');
        }

        janitor()->data($cli->arg('command'), [
            'status' => 200,
        ]);
    },
];
