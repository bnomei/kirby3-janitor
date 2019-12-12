<?php

declare(strict_types=1);

namespace Bnomei;

final class ContextJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        return [
            'status' => 200,
            'label' => $this->page()->title()->value() . ' ' . $this->data(),
        ];
    }
}
