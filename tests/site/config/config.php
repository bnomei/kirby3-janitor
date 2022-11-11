<?php

return [
    'debug' => true,
    'languages' => true,

    // janitor v2 job callback
    'some.key.to.an.task' => function (\Kirby\Cms\Page $page, $data = null) {
        return [
            'status' => 200,
            'message' => $page->uuid() . ' ' . $data,
        ];
    },
];
