<?php

// show image to generate thumb
foreach($page->images() as $image) {
    echo $image->thumb(['width' => 128]);
}
