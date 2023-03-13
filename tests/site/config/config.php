<?php

return [
    'debug' => true,
    'languages' => true,

    'bnomei.janitor.secret' => 'e9fe51f94eadabf54',

    // janitor v2 job callback
    'some.key.to.task' => function ($model, $data = null) {
        return [
            'status' => 200,
            'message' => $model->uuid() . ' ' . $data,
            'help' => 'new help from api'
        ];
    },
    'random.colors' => function ($model, $data = null) {
        return [
            'status' => 200,
            'color' => '#'.dechex(rand(0x000000, 0xFFFFFF)), // random hex color
            'backgroundColor' => '#'.dechex(rand(0x000000, 0xFFFFFF)), // random hex color
        ];
    },

//    'bnomei.janitor.maintenance.check' => function() {
//        return kirby()->users()->current()?->role()->isAdmin() !== true;
//    },
];
