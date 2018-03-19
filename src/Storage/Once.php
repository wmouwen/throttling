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
    public function get(string $key)
    {
        return self::$values[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value): void
    {
        self::$values[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function lock(string $key): void
    {
        if (in_array($key, self::$locks)) {
            throw new ResourceNotAvailable;
        }

        self::$locks[] = $key;
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
