<?php

declare(strict_types=1);

use Kirby\CLI\CLI;

return [
    'description' => 'Run a REPL session',
    'args' => [],
    'command' => static function (CLI $cli): void {
        while (true) {
            eval($cli->input('>>> ')->prompt());
        }
    },
];
