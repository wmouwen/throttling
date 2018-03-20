<?php

namespace Sqlr\Throttling\Storage;

use Sqlr\Throttling\Exception\ResourceNotAvailable;

class Session implements StorageInterface
{
    const LOCKS = 'throttler-locks';
    const DATA = 'throttler-data';

    /**
     * @inheritdoc
     */
    public function get($items)
    {
        if (is_scalar($items)) {
            return $_SESSION[static::DATA][$items] ?? null;
        }

        if (is_array($items)) {
            return array_intersect_key($_SESSION[static::DATA], array_flip($items));
        }

        throw new \InvalidArgumentException;
    }

    /**
     * @inheritdoc
     */
    public function set($items, $value = null): void
    {
        if (is_scalar($items) && !is_null($value)) {
            $_SESSION[static::DATA][$items] = $value;
        }

        if (!is_array($items)) {
            throw new \InvalidArgumentException;
        }

        $_SESSION[static::DATA] = array_merge($_SESSION[static::DATA], $items);
    }

    /**
     * @inheritdoc
     */
    public function lock(string $key): void
    {
        if ($this->isLocked($key)) {
            throw new ResourceNotAvailable;
        }

        $_SESSION[static::LOCKS][] = $key;
    }

    /**
     * @inheritdoc
     */
    public function isLocked(string $key): bool
    {
        return in_array($key, $_SESSION[static::LOCKS] ?? []);
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
