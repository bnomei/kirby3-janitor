<?php

declare(strict_types=1);

namespace Bnomei;

final class ReindexSearchForKirbyJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        if (class_exists('\Kirby\Search\Index')) {
            try {
                (new \Kirby\Search\Index)->build();
            } catch (\Exception $e) {
                return [
                    'status' => 500,
                    'error' => $e->getMessage(),
                ];
            }
            return [
                'status' => 200,
            ];
        }

        return [
            'status' => 204,
        ];
    }
}

