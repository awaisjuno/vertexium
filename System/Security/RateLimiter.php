<?php

namespace System\Security;

use Predis\Client;

/**
 * Class RateLimiter
 *
 * Limits requests per IP or key to protect against bots.
 */
class RateLimiter
{
    protected Client $redis;
    protected int $limit;
    protected int $seconds;

    /**
     * RateLimiter constructor.
     *
     * @param int $limit Max requests
     * @param int $seconds Time window in seconds
     */
    public function __construct(int $limit = 500, int $seconds = 60)
    {
        $this->limit = $limit;
        $this->seconds = $seconds;

        // Initialize Redis
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
    }

    /**
     * Check if the IP can make a request
     *
     * @param string|null $key
     * @return bool
     */
    public function allow(string $key = null): bool
    {
        $key = $key ?? $_SERVER['REMOTE_ADDR'];

        $current = $this->redis->get($key);

        if (!$current) {
            $this->redis->setex($key, $this->seconds, 1);
            return true;
        }

        if ((int)$current >= $this->limit) {
            return false; // Limit reached
        }

        $this->redis->incr($key);
        return true;
    }

    /**
     * Apply limit, block if exceeded
     */
    public function enforce(): void
    {
        if (!$this->allow()) {
            http_response_code(429);
            exit('Too many requests. Please try again later.');
        }
    }
}
