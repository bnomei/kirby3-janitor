<?php

return [
    'debug' => true,
    'languages' => true,

    // janitor v2 job callback
    'some.key.to.task' => function ($model, $data = null) {
        return [
            'status' => 200,
            'message' => $model->uuid() . ' ' . $data,
        ];
    },
];
