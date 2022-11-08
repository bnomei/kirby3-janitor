<?php

declare(strict_types=1);


final class FlushPagesCacheJob extends \Bnomei\JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        return [
            'status' => kirby()->cache('pages')->flush() ? 200 : 404,
        ];
    }
}
