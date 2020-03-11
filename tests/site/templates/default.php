<?php

// show image to generate thumb
if ($img = $page->file('test.jpg')) {
    echo $img->thumb(['width' => 128]);
}
