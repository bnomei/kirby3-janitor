<?php

use Bnomei\Janitor;

return [
    'bnomei.janitor.thumbs' => true,

    'bnomei.janitor.jobs' => [
        'page' => function (Kirby\Cms\Page $page = null, string $data = null) {
            // $page => page object where the button as pressed
            // $data => 'my custom data'
            return [
                'status' => 200,
                'label' => $page->title() . ' ' . $data,
            ];
        },

        'reload' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'reload' => true, // will trigger JS location.reload in panel
            ];
        },

        'clipboard' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'label' => 'Fetched. Click to Copy.',
                'clipboard' => 'Janitor',
            ];
        },

        'openurl' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'href' => 'https://github.com/bnomei/kirby3-janitor',
            ];
        },

        'openurlfromdata' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'href' => $data,
            ];
        },

        'download' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'download' => 'https://raw.githubusercontent.com/bnomei/kirby3-janitor/master/kirby3-janitor-screenshot-1.gif',
            ];
        },

        'minimal' => function () {
            return true;
        },

        'query' => function (Kirby\Cms\Page $page = null, string $data = null) {
            return [
                'status' => 200,
                'label' => $data,
            ];
        },

        'heist' => function (Kirby\Cms\Page $page = null, string $data = null) {
            \Bnomei\Janitor::singleton()->log('heist.mask ' . time());

            $grand = random_int(1, 9);
            sleep(1);
            // or trigger a snippets like this:
            // snippet('call-police');

            // $page is Kirby Page Object if job issued by Panel
            $location = $page ? $page->title() : 'Bank';

            // $data is optional [data] prop from the Janitor Panel Field
            $currency = $data ? $data : 'Coins';

            \Bnomei\Janitor::singleton()->log('heist.exit ' . time());
            return [
                'status' => $grand > 0 ? 200 : 404,
                'label' => $grand . ' ' . $currency . ' looted at ' . $location . '!'
            ];
        },

        'touchfile' => function (Kirby\Cms\Page $page = null, string $data = null) {
            $file = $page->file($data);
            if ($file) {
                touch($file->root());
            }
            return [
                'status' => $file ? 200 : 404,
                'label' => $file ? $file->modified() : $data,
            ];
        },
    ],

    'another.plugin.jobs' => [
        'whistle' => 'Bnomei\\WhistleJob',
        'context' => 'Bnomei\\ContextJob',
    ],
    'bnomei.janitor.jobs.extends' => [
        'another.plugin.jobs',
    ],


];
