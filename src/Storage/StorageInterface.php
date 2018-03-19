<?php

namespace Sqlr\Throttling\Storage;

use Sqlr\Throttling\Exception\ResourceNotAvailable;

interface StorageInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void;

    /**
     * @param string $key
     * @throws ResourceNotAvailable
     */
    public function lock(string $key): void;

    /**
     * @param string $key
     */
    public function free(string $key): void;
}
