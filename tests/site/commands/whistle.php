<?php

return [
    'description' => 'Whistle',
    'args' => [],
    'command' => static function ($cli): void {
        if (defined('STDOUT')) {
            $cli->success('whistle');
        }

//        janitor()->data('whistle', [
//            'status' => 200,
//            'label' => '♫',
//        ]);
    }
];
