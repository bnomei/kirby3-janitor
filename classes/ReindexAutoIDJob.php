<?php

declare(strict_types=1);

namespace Bnomei;

final class ReindexAutoIDJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $success = false;

        if (class_exists('\Bnomei\AutoID')) {
            $success = \Bnomei\AutoID::index(true) > 0;
        }

        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
