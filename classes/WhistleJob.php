<?php

declare(strict_types=1);

namespace Bnomei;

final class WhistleJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        return [
            'status' => 200,
            'label' => '♫',
        ];
    }
}
