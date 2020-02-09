<?php

declare(strict_types=1);

namespace Bnomei;

final class FlushRedisDBJob extends JanitorJob
{
    /**
     * @return array
     */
    public function job(): array
    {
        $success = false;

        if (class_exists('\Bnomei\Redis')) {
            // DANGER: $this->connection->flushdb()
            $success = (new \Bnomei\Redis())->flush();
        }

        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
