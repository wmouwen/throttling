<?php

namespace Sqlr\Throttling\Storage;

use Sqlr\Throttling\Exception\ResourceNotAvailable;

class Session implements StorageInterface
{
    const LOCKS = 'throttler-locks';

    /**
     * @inheritdoc
     */
    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function lock(string $key): void
    {
        if (in_array($key, $_SESSION[static::LOCKS] ?? [])) {
            throw new ResourceNotAvailable;
        }

        $_SESSION[static::LOCKS][] = $key;
    }

    /**
     * @inheritdoc
     */
    public function free(string $key): void
    {
        $location = array_search($key, $_SESSION[static::LOCKS] ?? []);

        if ($location !== false) {
            unset($_SESSION[static::LOCKS][$location]);
        }
    }
}
