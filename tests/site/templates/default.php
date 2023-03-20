<?php

echo site()->url();

echo $page::class;

// show image to generate thumb
foreach ($page->images() as $image) {
	echo $image->thumb(['width' => 128]);
}

//\Kirby\CLI\CLI::command('whistle');
//var_dump(janitor()->data('whistle'));
// throw new Exception('xx');
