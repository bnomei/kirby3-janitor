<?php

declare(strict_types=1);

if (! class_exists('Bnomei\Janitor')) {
    require_once __DIR__.'/../classes/Janitor.php';
}

use Kirby\CLI\CLI;

return [
    'description' => 'Flush a cache',
    'args' => [
        'name' => [
            'description' => 'Name of the cache',
        ],
    ],
    'command' => static function (CLI $cli): void {
        $name = $cli->argOrPrompt('name', 'Which cache should be emptied? (press <Enter> to clear the pages cache)', false);
        $name = empty($name) ? 'pages' : $name;

        $cli->kirby()->cache($name)->flush();

        $cli->success('The cache "'.$name.'" has been cleared.');

        janitor()->data($cli->arg('command'), [
            'status' => 200,
        ]);
    },
];
