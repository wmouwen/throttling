<?php

namespace Sqlr\Throttling\Storage;

use Sqlr\Throttling\Exception\ResourceNotAvailable;

interface StorageInterface
{
    /**
     * Retrieves one or more items.
     *
     * @param string $items
     * @return mixed
     */
    public function get($items);

    /**
     * Stores one or more items.
     *
     * Store a single item:
     *     $storage->set('foo', 'bar')
     *
     * Store multiple items:
     *     $storage->set(['foo' => 'bar', 'second' => 'baz', ...])
     *
     * @param $items
     * @param null|mixed $value
     */
    public function set($items, $value = null): void;

    /**
     * @param string $key
     * @throws ResourceNotAvailable
     */
    public function lock(string $key): void;

    /**
     * @param string $key
     * @return bool
     */
    public function isLocked(string $key): bool;

    /**
     * @param string $key
     */
    public function free(string $key): void;
}
