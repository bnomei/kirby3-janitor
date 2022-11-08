<?php

const KIRBY_HELPER_DUMP = false;
require_once __DIR__ . '/../vendor/autoload.php';
echo (new Kirby())->render();
