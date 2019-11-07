<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\F;
use Symfony\Component\Finder\Finder;

final class CleanSessionsJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $success = kirby()->app()->session()->store()->collectGarbage();
        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
