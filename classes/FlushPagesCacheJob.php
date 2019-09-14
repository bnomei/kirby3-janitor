<?php

declare(strict_types=1);

namespace Bnomei;

final class FlushPagesCacheJob extends JanitorJob
{
    /**
     * @return bool
     */
    public function job(): array
    {
        return [
            'status' => kirby()->cache('pages')->flush() ? 200 : 404,
        ];
    }
}
