<?php

return [
    'description' => 'Run a REPL session',
    'args' => [],
    'command' => static function ($cli): void {
        while (true) {
            eval($cli->input('>>> ')->prompt());
        }
    }
];
