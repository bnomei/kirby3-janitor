<?php

namespace Bnomei;

use League\CLImate\Util\Writer\WriterInterface;

class QuietWriter implements WriterInterface
{

    public function write($content)
    {
        // be quiet here
    }
}
