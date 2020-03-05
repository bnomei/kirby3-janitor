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
            $redis = new \Bnomei\Redis();
            if ($redis->redisClient()->dbsize() > 1) {
                // DANGER: $this->connection->flushdb()
		        $redis->flush();
                $success = $redis->redisClient()->dbsize() === 0;
            }
        }

        return [
            'status' => $success ? 200 : 204,
        ];
    }
}
