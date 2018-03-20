<?php

namespace Sqlr\Throttling\Storage;

use Sqlr\Throttling\Exception\ResourceNotAvailable;

class Once implements StorageInterface
{
    /**
     * @var array
     */
    private static $values = [];

    /**
     * @var array
     */
    private static $locks = [];

    /**
     * @inheritdoc
     */
    public function get($items)
    {
        if (is_scalar($items)) {
            return self::$values[$items] ?? null;
        }

        if (is_array($items)) {
            return array_intersect_key(self::$values, array_flip($items));
        }

        throw new \InvalidArgumentException;
    }

    /**
     * @inheritdoc
     */
    public function set($items, $value = null): void
    {
        if (is_scalar($items) && !is_null($value)) {
            $items = [$items => $value];
        }

        if (!is_array($items)) {
            throw new \InvalidArgumentException;
        }

        self::$values = array_merge(self::$values, $items);
    }

    /**
     * @inheritdoc
     */
    public function lock(string $key): void
    {
        if ($this->isLocked($key)) {
            throw new ResourceNotAvailable;
        }

        self::$locks[] = $key;
    }

    /**
     * @inheritdoc
     */
    public function isLocked(string $key): bool
    {
        return in_array($key, self::$locks);
    }

    /**
     * @inheritdoc
     */
    public function free(string $key): void
    {
        $location = array_search($key, self::$locks);

        if ($location !== false) {
            unset(self::$locks[$location]);
        }
    }
}
