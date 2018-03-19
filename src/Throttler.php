<?php

namespace Sqlr\Throttling;

use Sqlr\Throttling\Exception\RequestThrottled;
use Sqlr\Throttling\Exception\ResourceNotAvailable;
use Sqlr\Throttling\Storage\StorageInterface;

abstract class Throttler
{
    /**
     * Connection with a storage backend
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Key used to store values in storage interfaces
     *
     * @var string
     */
    protected $key = 'throttler';

    /**
     * Time period in seconds
     *
     * @var int
     */
    protected $period = 1;

    /**
     * Amount of tokens available/replenished over the span of a period
     *
     * @var int
     */
    protected $capacity = 1;

    /**
     * Whether the claiming of resources should exclude others from attempting
     *
     * @var bool
     */
    protected $locking = false;

    /**
     * LeakyBucket constructor.
     *
     * @param StorageInterface $storage
     * @param array $options
     */
    function __construct(StorageInterface $storage, array $options = [])
    {
        $this->setStorage($storage);

        if (array_key_exists('key', $options)) {
            $this->setKey($options['key']);
        }

        if (array_key_exists('period', $options)) {
            $this->setPeriod($options['period']);
        }

        if (array_key_exists('capacity', $options)) {
            $this->setCapacity($options['capacity']);
        }

        if (array_key_exists('locking', $options)) {
            $this->setLocking($options['locking']);
        }
    }

    /**
     * @param int $resources
     * @return bool
     */
    abstract public function available(int $resources = 1): bool;

    /**
     * @param int $resources
     * @throws RequestThrottled
     * @throws ResourceNotAvailable
     */
    abstract public function claim(int $resources = 1): void;

    /**
     * @param int $resources
     */
    protected function assertRequestedResources(int $resources)
    {
        if ($resources > $this->capacity) {
            throw new \InvalidArgumentException('Requested more resources than the capacity allows.');
        }
        if (0 > $resources) {
            throw new \InvalidArgumentException('Negative amount of resources requested.');
        }
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     * @return Throttler
     */
    public function setStorage(StorageInterface $storage): Throttler
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Throttler
     */
    public function setKey(string $key): Throttler
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod(): int
    {
        return $this->period;
    }

    /**
     * @param int $period
     * @return Throttler
     */
    public function setPeriod(int $period): Throttler
    {
        $this->period = $period;
        return $this;
    }

    /**
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     * @return Throttler
     */
    public function setCapacity(int $capacity): Throttler
    {
        $this->capacity = $capacity;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocking(): bool
    {
        return $this->locking;
    }

    /**
     * @param bool $locking
     * @return Throttler
     */
    public function setLocking(bool $locking): Throttler
    {
        $this->locking = $locking;
        return $this;
    }
}
