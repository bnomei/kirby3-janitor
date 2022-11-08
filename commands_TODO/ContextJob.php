<?php

declare(strict_types=1);


final class ContextJob extends \Bnomei\JanitorJob
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
