<?php

function patchKirbyHelpers()
{
    $h = __DIR__.'/kirby/config/helpers.php';
    if (file_exists($h)) {
        // open file and change a function name dump to xdump and save file again
        $content = file_get_contents($h);
        $content = str_replace('function dump(', 'function xdump(', $content);
        $content = str_replace('function e(', 'function xe(', $content);
        file_put_contents($h, $content);
    }
}
patchKirbyHelpers();
