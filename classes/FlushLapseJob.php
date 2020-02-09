<?php

declare(strict_types=1);

namespace Bnomei;

final class FlushLapseJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $success = false;

        if (class_exists('\Bnomei\Lapse')) {
            $success = \Bnomei\Lapse::singleton()->flush();
        }

        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
